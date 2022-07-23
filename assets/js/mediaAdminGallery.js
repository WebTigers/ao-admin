/**
 * jQuery 3AO Media Manage Dashboard Plugin
 */
(function( $ ){

    let Class = {

        /** Dropzone plugin globals. */
        dropzone            : null,
        clipboard           : null,
        galleryInsert       : false,
        configs             : null,
        initComplete        : false,

        serverMaxFileSize   : null,
        displayMaxFileSize  : null,
        imageMaxWidth       : 6000,
        imageMaxHeight      : 6000,

        init : function( ) {

            $(document).ready(function() {

                Class.serverMaxFileSize = parseInt( $('#metadata').attr('data-server-maxfilesize'), 10 );
                Class.displayMaxFileSize = parseInt( Class.serverMaxFileSize/1000000, 10 );

                Class._initControls();
                Class._initUploader();
                Class._fetchMedia();
                Class._initDelete();
                Class._initEdit()

            });

        },

        // Admin Functions //

        _initControls : function ( ) {

            /**
             * Init the prettyPhoto library
             * http://www.no-margin-for-errors.com/projects/prettyphoto-jquery-lightbox-clone/
             */
            $("a[rel^='prettyPhoto']").prettyPhoto({
                social_tools : false,
                theme        : 'light_square'
            });

            $('#save-media-button').on( 'click', Class._updateImage );
            // $('#refresh-gallery').on( 'click', Class._refreshGallery );

            /** https://clipboardjs.com */
            Class.clipboard = new ClipboardJS('button.img-copy');
            Class.clipboard.on('success', function( event ) {
                $(event.trigger).attr('title', $(event.trigger).attr('data-title')).tooltip('show');
                setTimeout(function (){
                    $(event.trigger).tooltip('hide');
                }, 1000);
                setTimeout(function (){
                    $(event.trigger).removeAttr('title').tooltip('dispose');
                }, 2000);
            });

        },

        _initUploader : function ( ) {

            /**
             * Init the Dropzone library
             * https://www.dropzonejs.com/#configuration-options
             * https://gitlab.com/meno/dropzone/-/wikis/FAQ
             */
            Class.dropzone = $('#upload-zone').addClass('dropzone').dropzone({

                url             : '/wp-admin/admin-ajax.php',
                addRemoveLinks  : true,
                acceptedFiles   : '.jpg,.jpeg,.png,.tif,.tiff',
                maxFilesize     : Class.serverMaxFileSize,

                maxThumbnailFilesize : 50,      // this needs to be set the same as the maxFilesize in Mb.

                parallelUploads : 1,            // since we're using a global 'currentFile', we could have issues if parallelUploads > 1, so we'll make it = 1
                chunking        : true,         // enable chunking
                forceChunking   : true,         // forces chunking when file.size < chunkSize
                parallelChunkUploads : true,    // allows chunks to be uploaded in parallel (this is independent of the parallelUploads option)
                chunkSize       : 1000000,      // chunk size 1,000,000 bytes (~1MB)
                retryChunks     : true,         // retry chunks on failure

                init : function ( ) {

                    let DZ = this;

                    DZ.on( 'thumbnail', function ( file, a, b ) {

                        if ( file.width > Class.imageMaxWidth || file.height > Class.imageMaxHeight ) {
                            file.rejectDimensions();
                        }
                        else if ( file.size > DZ.options.maxFilesize ) {
                            file.rejectFilesize();
                        }
                        else if ( file.type.indexOf('image') > 0 || file.type.indexOf('pdf') > 0 ) {
                            file.rejectFileType();
                        }
                        else {
                            file.acceptFile();
                        }
                    });

                    DZ.on( 'success', function ( file, data ) {

                        if ( data.result === 1 ) {
                            Class.galleryInsert = true;
                            Class._addToGallery(data.data);
                            DZ.removeFile( file );
                        }
                        else {
                            // Show invalid message from data.messages
                            console.error( file, data );
                        }

                    });

                    DZ.on( 'error', function( file, errorMessage, xhr ) {
                        console.error( file, errorMessage, xhr );
                    });

                },
                accept : function ( file, done ) {

                    let DZ = this;

                    file.acceptFile = done;

                    file.rejectDimensions = function ( ) {
                        done( DZ.options.dictFileDimensions.replace( "{{height}}", Class.imageMaxHeight ).replace( "{{width}}", Class.imageMaxWidth ) );
                    };

                    file.rejectFilesize = function ( ) {
                        done( DZ.options.dictFileTooBig.replace("{{filesize}}", Math.round( file.size / 1024 / 10.24) / 100).replace("{{maxFilesize}}", Class.displayMaxFileSize ) );
                    };

                    file.rejectFileType = function ( ) {
                        done( DZ.options.dictInvalidFileType );
                    };

                },
                params : function ( files, xhr, chunk ) {

                    if ( chunk ) {

                        return {
                            action  : 'ao_api',
                            service : 'AOServiceMedia',
                            method  : 'uploadMedia',

                            dzUuid              : chunk.file.upload.uuid,
                            dzChunkIndex        : chunk.index,
                            dzTotalFileSize     : chunk.file.size,
                            dzCurrentChunkSize  : chunk.dataBlock.data.size,
                            dzTotalChunkCount   : chunk.file.upload.totalChunkCount,
                            dzChunkByteOffset   : chunk.index * this.options.chunkSize,
                            dzChunkSize         : this.options.chunkSize,
                            dzFilename          : chunk.file.name,
                        };

                    }

                },
                error  : function ( file, message ) {
                    $( file.previewElement ).addClass("dz-error").find('.dz-error-message').text( message );
                },
                chunksUploaded : function ( file, done ) {
                    done();
                },

                // Dictionary //

                dictDefaultMessage : "<h4>Click here or drop files here to upload.</h4><br>Maximum file size is " + Class.displayMaxFileSize + "MB.",
                dictFallbackMessage : "Your browser does not support drag'n'drop file uploads.",
                dictFallbackText : "Please use the fallback form below to upload your files like in the olden days.",
                dictFileDimensions : 'Max image size is H {{height}} x W {{width}} pixels.',
                dictFileTooBig : "File is too big ({{filesize}} MB). Max filesize: {{maxFilesize}} MB.",
                dictInvalidFileType : "Only .jpg, .png, and .pdf files are allowed.",
                dictResponseError : "Server responded with {{statusCode}} code.",
                dictCancelUpload : "Cancel upload",
                dictCancelUploadConfirmation : "Are you sure you want to cancel this upload?",
                dictRemoveFile : "Remove file",
                dictMaxFilesExceeded : "You can not upload any more files."

            });

        },

        _fetchMedia : function ( ) {

            function success ( data, textStatus, jqXHR ) {

                /** Result Success / Error */

                if ( data.result === 1 ) {

                    Class._addToGallery( data.data );

                }
                else {

                    /** Oops, something went wrong ... */

                    $( '#page-messages' ).css('overflow','hidden').tigerDOM( 'change', {
                        content       : data.html[0],
                        removeClick   : true,
                        removeTimeout : 0
                    });

                }

            }

            function error ( jqXHR, textStatus, errorThrown ) {

                // show general error message
                let oMessage = {
                    content       : '<div class="alert alert-danger"><i class="fa fa-ban"></i> &nbsp;' + errorThrown + '</div>',
                    removeClick   : true,
                    removeTimeout : 0
                };

                $( '#page-messages' ).css('overflow','hidden').tigerDOM( 'change', oMessage );

            };

            let data = {
                action  : 'ao_api',
                service : 'AOServiceMedia',
                method  : 'getImageGallery'
            };

            $().aoAdmin('api', data, success );

        },

        _refreshGallery : function ( ) {

            $('#gallery').children().hide(400, function(){
                $(this).remove();
            });
            setTimeout( function (){
                $().mediaAdminGallery( '_fetchMedia' );
            }, 1000);

        },

        _addToGallery : function ( data ) {

            $( data ).each( function( i, data ) {
                Class._buildMedia( data );
            });

            $('.img-lightbox').on('click.lightbox', function ( event ) {

                let $img = $(this).closest('div.options-container').find('img');

                $.prettyPhoto.open(
                    $img.attr('data-full-size'),
                    $img.attr('title'),
                    $img.attr('alt')
                );

            });

            $().aoAdmin('refreshThumbnails');
        },

        _removeFromGallery : function ( id ) {

            $('#gallery').find('img[id=' + id + ']').closest('.image-container').remove();

        },

        _buildMedia : function ( data ) {

            // console.log( data );

            /** Get the template as a jQuery object ... */
            let $galleryTemplate = $('#gallery-template').first().clone();

            /**
             {
                 ID: 9
                 comment_count: "0"
                 comment_status: "open"
                 filter: "raw"
                 guid: "https://3ao-beaudev.com/wp-content/uploads/2022/07/Everything-I-Need.jpg"
                 menu_order: 0
                 ping_status: "closed"
                 pinged: ""
                 post_author: "1"
                 post_content: ""
                 post_content_filtered: ""
                 post_date: "2022-07-02 16:50:10"
                 post_date_gmt: "2022-07-02 16:50:10"
                 post_excerpt: ""
                 post_mime_type: "image/jpeg"
                 post_modified: "2022-07-02 16:50:10"
                 post_modified_gmt: "2022-07-02 16:50:10"
                 post_name: "everything-i-need"
                 post_parent: 0
                 post_password: ""
                 post_status: "inherit"
                 post_title: "Everything-I-Need"
                 post_type: "attachment"
                 to_ping: ""
             }
             */

            $galleryTemplate.find('img')
                .attr('id', data.ID)
                .attr('src', data.thumbnail)
                .attr('data-extension', data.extension)
                .attr('title', data.post_title)
                .attr('alt', data.post_excerpt)
                .attr('data-full-size', data.guid);

            $galleryTemplate.find('button.img-copy').attr( 'data-clipboard-text', data.guid );

            if ( data.post_title ) {
                $galleryTemplate.find('.caption-title').html(data.post_title);
            }

            if ( data.post_excerpt ) {
                $galleryTemplate.find('.description').html( data.post_excerpt );
            }

            if ( Class.galleryInsert === false ) {
                $('#gallery').append($galleryTemplate.html());
            }
            else {
                $('#gallery').prepend($galleryTemplate.html());
                Class.galleryInsert = false;
            }
        },

        _initDelete : function () {

            $('#gallery').on( 'click', '.img-delete', Class._deleteConfirm );

        },

        _deleteConfirm : function ( ) {

            let id  = $(this).closest('.image-container').find('img').attr('id');
            let src = $(this).closest('.image-container').find('img').attr('src')

            $('#delete-media-confirm-thumbnail').attr('src', src );
            $('#delete-media-modal-delete').attr('data-media-id', id );

            /** Attach the proper listener for when the delete button is pressed. */
            $('#delete-media-modal-delete').off().on( 'click', Class._deleteImage );

            $('#deleteModal').modal('show');

        },

        _deleteImage : function ( event ) {

            function success ( data, textStatus, jqXHR ) {

                /** Result Success / Error */

                if ( data.result === 1 ) {

                    $('#deleteModal').modal('hide');
                    Class._removeFromGallery( $('#delete-media-modal-delete').attr('data-media-id') );

                }
                else {

                    /** Oops, something went wrong ... */

                    $( '#delete-image-modal-messages' ).css('overflow','hidden').tigerDOM( 'change', {
                        content       : data.html[0],
                        removeClick   : true,
                        removeTimeout : 0
                    });

                }

            }

            function error ( jqXHR, textStatus, errorThrown ) {

                // show general error message
                let oMessage = {
                    content       : '<div class="alert alert-danger"><i class="fa fa-ban"></i> &nbsp;' + errorThrown + '</div>',
                    removeClick   : true,
                    removeTimeout : 0
                };

                $( '#page-messages' ).css('overflow','hidden').tigerDOM( 'change', oMessage );

            };

            let data = {
                action  : 'ao_api',
                service : 'AOServiceMedia',
                method  : 'deleteMedia',
                id      : $(this).attr('data-media-id')
            };

            $().aoAdmin('api', data, success );

        },

        _initEdit : function ( ) {

            $('#gallery').on( 'click', '.img-edit', Class._editImage );

        },

        _populateMediaMeta : function ( data ) {

            $('#edit-media-thumbnail').attr( 'src', data.data.media_guid );

            $('#mediaEditModal .uploaded').html( moment( data.data.media_post_date ).format('MMM-DD-YYYY') );
            $('#mediaEditModal .filename').html( data.data.media_guid.split('/').slice(-1) );
            $('#mediaEditModal .file-type').html( data.data.media_mime_type );
            $('#mediaEditModal .file-size').html( data.element.filesize );
            $('#mediaEditModal .image-dimensions').removeClass('hide').html( data.element.height + ' H x ' + data.element.width + ' W' );

        },

        _editImage : function ( ) {

            function success ( data, textStatus, jqXHR ) {

                /** Result Success / Error */

                if ( data.result === 1 ) {

                    $('#AOFormMedia').resetForm();
                    $('#edit-media-modal-messages').css('height','0').children().remove();
                    $('#AOFormMedia').aoAdmin('setFormValues', data.data )

                    /** Set Image Metadata */
                    Class._populateMediaMeta( data );

                    /** Attach the proper listener for when the save button is pressed. */
                    $('#edit-media-modal-save').off().on( 'click', Class._updateImage );

                    $('#mediaEditModal').modal('show');

                }

                $( '.gallery-form-messages' ).css('overflow','hidden').tigerDOM( 'change', {
                    content       : data.html[0],
                    removeClick   : true,
                    removeTimeout : 0
                });

            }

            function error ( jqXHR, textStatus, errorThrown ) {

                // show general error message
                let oMessage = {
                    content       : '<div class="alert alert-danger"><i class="fa fa-ban"></i> &nbsp;' + errorThrown + '</div>',
                    removeClick   : true,
                    removeTimeout : 0
                };

                $( '#edit-media-modal-messages' ).css('overflow','hidden').tigerDOM( 'change', oMessage );

            };

            let data = {
                action  : 'ao_api',
                service : 'AOServiceMedia',
                method  : 'getFormData',
                id      : $(this).closest('.image-container').find('img').attr('id')
            };

            $().aoAdmin('api', data, success );

        },

        _updateImage : function ( event ) {

            function success ( data, textStatus, jqXHR ) {

                /** Result Success / Error */

                $( '#edit-media-modal-messages' ).css('overflow','hidden').tigerDOM( 'change', {
                    content       : data.html[0],
                    removeClick   : true,
                    removeTimeout : 0
                });

            }

            function error ( jqXHR, textStatus, errorThrown ) {

                // show general error message
                let oMessage = {
                    content       : '<div class="alert alert-danger"><i class="fa fa-ban"></i> &nbsp;' + errorThrown + '</div>',
                    removeClick   : true,
                    removeTimeout : 0
                };

                $( '#page-messages' ).css('overflow','hidden').tigerDOM( 'change', oMessage );

            };

            let data = {
                action  : 'ao_api',
                service : 'AOServiceMedia',
                method  : 'updateMedia',
                data    : $('#AOFormMedia').aoAdmin('getFormValues')
            };

            $().aoAdmin('api', data, success );

        }

    };

    $.fn.mediaAdminGallery = function( method ) {
        if ( Class[method] ) {
            return Class[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return Class.init.apply( this, arguments );
        } else {
            return $.error( 'Method ' +  method + ' does not exist.' );
        }
    };

    $().mediaAdminGallery();

})( jQuery );
