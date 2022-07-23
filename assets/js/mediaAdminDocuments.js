/**
 * jQuery 3AO Document Manage Dashboard Plugin
 */
(function( $ ){

    let Class = {

        /** Dropzone plugin globals. */
        dropzone            : null,
        clipboard           : null,
        documentInsert      : false,
        configs             : null,
        initComplete        : false,

        serverMaxFileSize   : null,
        displayMaxFileSize  : null,

        init : function( ) {

            $(document).ready(function() {

                Class.serverMaxFileSize = parseInt( $('#metadata').attr('data-server-maxfilesize'), 10 );
                Class.displayMaxFileSize = parseInt( Class.serverMaxFileSize/1000000, 10 );

                Class._initControls();
                Class._initDocumentUploader();
                Class._fetchDocuments();
                Class._initDeleteDocument();
                Class._initEditDocument()

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

        _initDocumentUploader : function ( ) {

            /**
             * Init the Dropzone library
             * https://www.dropzonejs.com/#configuration-options
             * https://gitlab.com/meno/dropzone/-/wikis/FAQ
             */
            Class.dropzone = $('#upload-document-zone').addClass('dropzone').dropzone({

                url             : '/wp-admin/admin-ajax.php',
                addRemoveLinks  : true,
                acceptedFiles   : '.pdf, */pdf',
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

                    DZ.on( 'addedfile', function ( file ) {

                        // console.log('addfile', file );

                        if ( file.type.indexOf('pdf') > 0 ) {
                            $( file.previewElement ).find('.dz-image img')
                                .attr('src', $('#metadata').attr('data-ao-images-url') + 'pdf.png');
                        }

                    });

                    DZ.on( 'thumbnail', function ( file, fileurl ) {

                        // console.log('thumbnail', file, fileurl );

                        if ( file.size > DZ.options.maxFilesize ) {
                            file.rejectFilesize();
                        }
                        else if ( file.type.indexOf('pdf') > 0 ) {
                            file.rejectFileType();
                        }
                        else {
                            file.acceptFile();
                        }

                    });

                    DZ.on( 'success', function ( file, data ) {

                        // console.log('success', file, data );

                        if ( data.result === 1 ) {
                            Class.documentInsert = true;
                            Class._addToDocumentsView([{
                                data    : data.data,
                                element : data.element
                            }]);
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

                    file.rejectFilesize = function ( ) {
                        done( DZ.options.dictFileTooBig.replace("{{filesize}}", Math.round( file.size / 1024 / 10.24) / 100).replace("{{maxFilesize}}", Class.displayMaxFileSize ) );
                    };

                    file.rejectFileType = function ( ) {
                        done( DZ.options.dictInvalidFileType );
                    };

                    done();

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

                dictDefaultMessage : "<h4>Click here or drop PDF files here to upload.</h4><br>Maximum file size is " + Class.displayMaxFileSize + "MB.",
                dictFallbackMessage : "Your browser does not support drag'n'drop file uploads.",
                dictFallbackText : "Please use the fallback form below to upload your files like in the olden days.",
                dictFileDimensions : 'Max image size is H {{height}} x W {{width}} pixels.',
                dictFileTooBig : "File is too big ({{filesize}} MB). Max filesize: {{maxFilesize}} MB.",
                dictInvalidFileType : "Only .pdf files are allowed.",
                dictResponseError : "Server responded with {{statusCode}} code.",
                dictCancelUpload : "Cancel upload",
                dictCancelUploadConfirmation : "Are you sure you want to cancel this upload?",
                dictRemoveFile : "Remove file",
                dictMaxFilesExceeded : "You can not upload any more files."

            });

        },

        _fetchDocuments : function ( ) {

            function success ( data, textStatus, jqXHR ) {

                /** Result Success / Error */

                if ( data.result === 1 ) {

                    Class._addToDocumentsView( data.data );

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
                method  : 'getDocumentGallery'
            };

            $().aoAdmin('api', data, success );

        },

        _addToDocumentsView : function ( data ) {

            // console.log( '_addToDocumentsView', data );

            $( data ).each( function( i, doc ) {
                Class._buildDocumentView( doc.data, doc.element );
            });

            $('.img-lightbox').on('click.lightbox', function ( event ) {

                let $img = $(this).closest('div.options-container').find('img');

                $.prettyPhoto.open(
                    $img.attr('data-document-url') + '?iframe=true&width=90%&height=90%',
                    $img.attr('title'),
                    $img.attr('alt')
                );

            });

            $().aoAdmin('refreshThumbnails');
        },

        _removeFromDocumentsView : function ( id ) {

            $('#documents-gallery').find('img[id=' + id + ']').closest('.image-container').remove();

        },

        _buildDocumentView : function ( doc, element ) {

            // console.log( doc, element );

            /** Get the template as a jQuery object ... */
            let $documentTemplate = $('#document-template').first().clone();

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

            $documentTemplate.find('img')
                .attr('id', doc.ID)
                .attr('src', doc.guid.split('/').slice(0, -1).join('/') + '/' + element.sizes.large.file )
                .attr('data-extension', doc.extension)
                .attr('title', doc.post_title)
                .attr('alt', doc.post_excerpt)
                .attr('data-document-url', doc.guid);

            $documentTemplate.find('button.img-copy').attr( 'data-clipboard-text', doc.guid );

            if ( doc.post_title ) {
                $documentTemplate.find('.caption-title').html(doc.post_title);
            }

            if ( doc.post_excerpt ) {
                $documentTemplate.find('.description').html( doc.post_excerpt );
            }

            if ( Class.documentInsert === false ) {
                $('#documents-gallery').append( $documentTemplate.html() );
            }
            else {
                $('#documents-gallery').prepend( $documentTemplate.html() );
                Class.documentInsert = false;
            }
        },

        _viewDocument : function ( ) {

            let uri = 'path/to/filename.pdf?iframe=true&height=80%&width=60%';

            $.fn.prettyPhoto({social_tools:null});
            $.prettyPhoto.open( uri, 'Title', 'Alt Description' );

        },

        _initDeleteDocument : function () {

            $('#documents-gallery').on( 'click', '.img-delete', Class._deleteDocumentConfirm );

        },

        _deleteDocumentConfirm : function ( ) {

            let id  = $(this).closest('.image-container').find('img').attr('id');
            let src = $(this).closest('.image-container').find('img').attr('src')

            $('#delete-media-confirm-thumbnail').attr('src', src );
            $('#delete-media-modal-delete').attr('data-media-id', id );

            /** Attach the proper listener for when the delete button is pressed. */
            $('#delete-media-modal-delete').off().on( 'click', Class._deleteDocument );

            $('#deleteModal').modal('show');

        },

        _deleteDocument : function ( event ) {

            function success ( data, textStatus, jqXHR ) {

                /** Result Success / Error */

                if ( data.result === 1 ) {

                    $('#deleteModal').modal('hide');
                    Class._removeFromDocumentsView( $('#delete-media-modal-delete').attr('data-media-id') );

                }
                else {

                    /** Oops, something went wrong ... */

                    $( '#delete-modal-messages' ).css('overflow','hidden').tigerDOM( 'change', {
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

        _initEditDocument : function ( ) {

            $('#documents-gallery').on( 'click', '.img-edit', Class._editDocument );

        },

        _populateDocumentMeta : function ( data, trigger ) {

            $('#edit-media-thumbnail').attr( 'src', $(trigger).closest('.image-container').find('img').attr('src') );

            $('#mediaEditModal .uploaded').html( moment( data.data.media_post_date ).format('MMM-DD-YYYY') );
            $('#mediaEditModal .filename').html( data.data.media_guid.split('/').slice(-1) );
            $('#mediaEditModal .file-type').html( data.data.media_mime_type );
            $('#mediaEditModal .file-size').html( data.element.filesize );
            $('#mediaEditModal .image-dimensions').addClass('hide');

        },

        _editDocument : function ( ) {

            let trigger = this;

            function success ( data, textStatus, jqXHR ) {

                /** Result Success / Error */

                if ( data.result === 1 ) {

                    $('#AOFormMedia').resetForm();
                    $('#AOFormMedia').aoAdmin('setFormValues', data.data )

                    /** Set Image Metadata */
                    Class._populateDocumentMeta( data, trigger );

                    /** Attach the proper listener for when the save button is pressed. */
                    $('#edit-media-modal-save').off().on( 'click', Class._updateDocument );

                    $('#mediaEditModal').modal('show');

                }

                $( '.document-gallery-form-messages' ).css('overflow','hidden').tigerDOM( 'change', {
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

                $( '#edit-document-modal-messages' ).css('overflow','hidden').tigerDOM( 'change', oMessage );

            };

            let data = {
                action  : 'ao_api',
                service : 'AOServiceMedia',
                method  : 'getFormData',
                id      : $(this).closest('.image-container').find('img').attr('id')
            };

            $().aoAdmin('api', data, success );

        },

        _updateDocument : function ( event ) {

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

    $.fn.mediaAdminDocuments = function( method ) {
        if ( Class[method] ) {
            return Class[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return Class.init.apply( this, arguments );
        } else {
            return $.error( 'Method ' +  method + ' does not exist.' );
        }
    };

    $().mediaAdminDocuments();

})( jQuery );
