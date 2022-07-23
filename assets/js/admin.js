/** Admin JS */
(function( $ ){

    let Class = {

        themeStyle : '',
        themeColor : '',
        defaultColor : '#3F47CC',
        jsColor : null,
        thumbnails : [],

        // Initialization Methods //

        init : function ( ) {

            $( document ).ready( function( ) {

                /** Removes FOUC */
                $('body').removeClass('invisible');

                Class.initStyles();
                Class.initControls();
                Class.initThemeStyle();
                Class.initThemeColor();
                Class.initColorPicker();
                Class.initProfileImagePicker();
                Class.initValidate();
                Class.autoPopulateForms();
                // Class.autoPopulateResume();
                Class.initAutoStageName();

            });

        },

        initControls : function ( ) {

            $('#profile-save, #size-card-save').on('click', Class.save );
            // $('#resume-save').on('click', Class.saveResume );
            // $('#resume').summernote({ airMode: true });
            $('.view-password').on('click', function ( event ) {

                let $this = $(this);
                $this.closest('.form-group').find('input').attr('type', function( index, attr ){
                    $this.find('i').toggleClass('icon-eye-close').toggleClass('icon-eye-open');
                    return (attr === 'text') ? 'password' : 'text';
                });

            });
            $('body').on('click', '.thumbnail-gallery img', Class.selectProfileImage );

            /** Media Edit Modal */
            $('#edit-media-modal-close, #delete-media-modal-close')
                .on( 'click', function ( ) { $(this).closest('.modal').modal('hide'); } );

        },

        initStyles : function ( ) {

            let root = document.documentElement;
            root.style.setProperty( '--themecolor', Class.defaultColor );

        },

        initThemeStyle : function ( ) {

            /**
             * Check for a theme-style cookie
             * https://github.com/js-cookie/js-cookie
             */
            Class.themeStyle = Cookies.get('themeStyle');

            /** Set default themeStyle if none has been set. */
            if ( Class.themeStyle === undefined ) {

                Class.themeStyle = '';
                Cookies.set( 'themeStyle', '', { expires: 1825 } );

            }
            else {

                Class.themeStyle = Cookies.get('themeStyle');

                if ( ! $('body').hasClass( Class.themeStyle ) ) {
                    $('body').addClass( Class.themeStyle );
                }

                $('input:radio[name="theme-style"][value="' + Class.themeStyle + '"]').prop( 'checked', true );

            }

            Class.setLogoStyle();

            $('input:radio[name="theme-style"]').on('change', function ( event ) {

                Class.themeStyle = $('input:radio[name="theme-style"]:checked').val();

                if ( Class.themeStyle ) {
                    $('body').addClass( Class.themeStyle );
                }
                else {
                    /** Remove all style classes that might be possible. */
                    $('body').removeClass( 'dark' );
                }

                Cookies.set( 'themeStyle', Class.themeStyle, { expires: 1825 } );
                Class.setLogoStyle();

            });

        },

        setLogoStyle : function ( ) {
            let logo = ( $('body').hasClass('dark') ) ? 'data-dark-logo' : 'data-light-logo';
            $('#logo img').attr('src', function(){ return $(this).closest('a').attr( logo ); } );
        },

        initThemeColor : function ( ) {

            /**
             * Check for a theme-style cookie
             * https://github.com/js-cookie/js-cookie
             */
            Class.themeColor = Cookies.get('themeColor');

            /** Set default themeColor if none has been set. */
            if ( Class.themeColor === undefined ) {

                Class.themeColor = Class.defaultColor;
                Cookies.set( 'themeColor', Class.themeColor, { expires: 1825 } );

            }

            document.documentElement.style.setProperty( '--themecolor', Class.themeColor );

        },

        initColorPicker : function ( ) {

            /** https://jscolor.com */

            Class.colorPicker = new JSColor( document.getElementById('js-colorpicker'), {
                preset : 'dark',
                width : 250,
                paletteCols : 15,
                palette : [
                    '#000000', '#7D7D7D', '#870014', '#EC1C23',
                    '#FF7E26', '#FEF100', '#22B14B', '#00A1E7',
                    '#3F47CC', '#A349A4', '#FFFFFF', '#C3C3C3',
                    '#B87957', '#FEAEC9', '#FFC80D', '#EEE3AF',
                    '#B5E61D', '#99D9EA', '#7092BE', '#C8BFE7',
                ],
                onChange : Class._updateColor,
                onInput : Class._updateColor
            });

            Class.colorPicker.fromString( Class.themeColor );

        },

        _updateColor : function ( ) {

            Class.themeColor = this.toRGBAString();
            document.documentElement.style.setProperty( '--themecolor', Class.themeColor );
            Cookies.set( 'themeColor', Class.themeColor, { expires: 1825 } );

        },

        initValidate : function ( ) {

            /**
             * Initializes each form field so that it
             * saves on blur or change for checkboxes.
             */

            $('input, textarea')
                .not('input[type=checkbox], input[type=radio]').on('blur', Class.validate );
            $('select, input[type=checkbox], input[type=radio]')
                .not('#theme-style-light, #theme-style-dark').on('change', Class.validate );

        },

        initProfileImagePicker : function ( ) {

            /** First, let's set the profile image if we have one set from the profile form. */

            let imgURL = ( $('#profile_image').val() )
                ? $('#profile_image').val()
                : $('#ao-dash-profile-image').attr('data-default-image-src');
            $('#ao-dash-profile-image').attr('src', imgURL );

            /** Now instantiate the profile image picker listener. */
            $('#ao-dash-profile-image').popover({
                title : 'Select a main profile image.',
                content : Class.imagePickerContent,
                html : true,
                placement : 'right'
            });

        },

        refreshThumbnails : function ( ) {

            Class.thumbnails = [];

            /** Setup a placeholder image. */
            let src = '/wp-content/plugins/ao-admin/assets/images/pattern2.png';
            let $img = $('<img>').attr('src', src);
            Class.thumbnails.push( $img );

            $('#gallery').children().each( function ( i, el ) {
                let src = $(el).find('img').attr('src');
                let $img = $('<img>').attr('src', src);
                Class.thumbnails.push( $img );
            });

        },

        imagePickerContent : function ( ) {

            let $gallery = $('<ul class="thumbnail-gallery">');

            if ( Class.thumbnails.length > 0 ) {
                $( Class.thumbnails ).each( function ( i, $el ) {
                    $gallery.append( $('<li>').append( $el ) );
                });
                return $('<div>').append( $gallery ).html()
            }
            else {
                return '<p>No images available.</p>';
            }

        },

        selectProfileImage : function ( event ) {

            $('#ao-dash-profile-image').attr('src', $(this).attr('src') );
            $('#profile_image').val( $(this).attr('src') );
            $('#profile-save').trigger('click');

        },

        initAutoStageName : function ( ) {

            function updateStageName ( ) {
                let stagename = $('#first_name').val() + ' ' + $('#last_name').val();
                $('#stage_name').val( stagename );
                $('#header-stage-name').html( stagename );
            }

            $('#first_name').on('keyup', updateStageName );
            $('#last_name').on('keyup', updateStageName );
            $('#stage_name').on('keyup', function ( ) {
                $('#header-stage-name').html( $(this).val() );
            });

        },

        //// API Operational Functions ////

        beforeSend : function ( jqXHR, settings ) {

        },

        success : function ( data, textStatus, jqXHR ) {

        },

        error : function ( jqXHR, textStatus, errorThrown ) {

        },

        complete : function ( jqXHR, textStatus ) {

        },

        /**
         * 3AO API
         * @param data
         * @param success
         * @param error
         * @param beforeSend
         * @param complete
         * @param url
         * @param type
         */
        api : function (
            data = {}, // Required
            success = Class.success,
            error = Class.error,
            beforeSend = Class.beforeSend,
            complete = Class.complete,
            url = '/wp-admin/admin-ajax.php',   // No need to touch
            type = 'POST'                       // No need to touch
        ) {

            $.ajax({
                type        : type,
                dataType    : "json",
                url         : url,
                data        : data,
                beforeSend  : beforeSend,
                success     : success,
                error       : error,
                complete    : complete
            });

        },

        validate : function ( event ) {

            Class.api({
                    action  : 'ao_api',
                    service : 'AOServiceValidation',
                    method  : 'validate',
                    form    : $(this).closest('form').attr('name'),
                    element : $(this).attr('name'),
                    value   : $(this).val()
                },
                Class.validateSuccess
            );

        },

        save : function ( event ) {

            let data = {
                action  : 'ao_api',
                service : $(this).closest('form').attr('data-service'),
                method  : 'save',
                data    : $(this).closest('form').aoAdmin('getFormValues')
            };

            Class.api(
                data,
                Class.saveFormSuccess
            );

        },

        saveResume : function ( event ) {

            let data = {
                action  : 'ao_api',
                service : 'AOServiceProfile',
                method  : 'saveResume',
                data    : { resume : $('#resume').summernote('code') }
            };

            Class.api(
                data,
                Class.saveFormSuccess
            );

        },

        validateSuccess : function ( data ) {

            Class.setFormMessages( data );

        },

        setFormMessages : function ( data ) {

            // console.log( data );
            if ( data.result === 0 ) {

                let msgData = {
                    result: 0,
                    form: data.form,
                    element: null,
                    messages: []
                };

                $.each( data.messages, function ( element, messages ) {

                    msgData.element = element;
                    msgData.messages = [];

                    $.each(messages, function ( errName, errMessage ) {
                        msgData.messages.push( { message: errMessage, error: errName } );
                    });

                    Class.setElementMessage( msgData );

                });

            }
            else {

                Class.setElementMessage( data );

            }

        },

        setElementMessage : function ( data ) {

            let $form = ( typeof data.form === 'string' )
                ? $('form[name="' + data.form + '"]' )
                : $( data.form );

            let $element = ( typeof data.element === 'string' )
                ? $form.find( '#' + data.element )
                : $(data.element);

            if ( data.result === 0 ) {

                let content = '<div class="alert alert-danger" role="alert"><i class="icon-warning-sign"></i>&nbsp; ' + data.messages[0].message + '</div>';

                $element.addClass('is-invalid');
                $element.closest('div.form-group').find('.message-container')
                    .addClass('invalid-feedback')
                    .tigerDOM('change', { content : content } );

            }
            else {

                $element.closest('div.form-group').find('.message-container')
                    .tigerDOM('change', {
                        content : '',
                        callback : function ( ) {
                            $element.removeClass( 'is-invalid' ).addClass( 'is-valid' );
                            setTimeout( function ( ) { $element.removeClass( 'is-valid'); }, 2000 );
                        }
                    });

            }

        },

        getFormValues : function ( formValues ) {

            if ( formValues === undefined ) {
                formValues = {};
            }

            let value = null;

            $( this ).find(':input').each(function (i, el) {

                if ( $(el).is(':checkbox') || $(el).is(':radio') ) {
                    value = ( $(el).is(':checked') ) ? 1 : 0
                }
                else {
                    value = el.value;
                }

                formValues[el.name] = value

            });

            return formValues;

        },

        saveFormSuccess : function ( data ) {

            let $form = $( '#' + data.form );

            if ( data.html ) {

                $form.find('.form-messages')
                    .css('overflow', 'hidden')
                    .tigerDOM('change', {
                        content: data.html[0],
                        removeClick: true,
                        removeTimeout: 5000
                    });

            }

            if ( data.messages ) {

                Class.setFormMessages( data );

            }

            Class.setPageLabels();

        },


        //// Populate Forms ////

        autoPopulateForms : function ( form = null ) {

            let $forms = ( form ) ? $(form) : $('form.auto-populate');

            $forms.each( function ( i, form ) {

                Class.api({
                        action  : 'ao_api',
                        service : $(form).attr('data-service'),
                        method  : 'getFormData'
                    },
                    function ( data ) {
                        $(form).aoAdmin('setFormValues', data.data );
                    }
                );

            });

        },

        setFormValues : function ( formValues ) {

            /**
             * Call this function as: $('#'+formName).aoAdmin( 'setFormValues', formValuesArray );
             * "this" is already a jQuery from object.
             */

            for ( let key in formValues ) {

                let $el = $(this).find('[name="' + key + '"]' );

                if ( $el.length > 0 && formValues[key] ) {

                    if ( $el.not('input:checkbox, input:radio').is('input:text, input:hidden, input[type=email], input[type=password], textarea') ) {
                        $el.val( formValues[key] );
                    }

                    if ( $el.is('input:checkbox, input:radio') ) {
                        if ( formValues[key] === $el.val() ) {
                            $el.attr('checked', 'checked').prop('checked', true);
                        }
                        else {
                            $el.removeAttr('checked').prop('checked', false);
                        }
                    }

                    if ( $el.is('select') ) {
                        $el.val( formValues[key] );
                        if ( $el.hasClass('select2') ) {
                            $el.trigger('change');
                        }
                    }

                }

            }

            Class.setPageLabels();


        },

        autoPopulateResume : function ( ) {

            Class.api({
                    action  : 'ao_api',
                    service : 'AOServiceProfile',
                    method  : 'getResumeData'
                },
                function ( data ) {
                    $('#resume').summernote('code', data.data );
                }
            );

        },

        setPageLabels : function ( ) {

            $('#header-stage-name').html( ( $('#stage_name').val() ) ? $('#stage_name').val() : '3AO Dashboard' );
            $('#ao-dash-profile-image').attr('src',
                ( $('#profile_image').val() )
                    ? $('#profile_image').val()
                    : $('#ao-dash-profile-image').attr('data-default-image-src')
            );

        }

    };

    $.fn.aoAdmin = function( method ) {
        if ( Class[method] ) {
            return Class[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return Class.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist.' );
        }
    };

    $().aoAdmin();

})( jQuery );
