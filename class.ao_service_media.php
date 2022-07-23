<?php

class AOServiceMedia {

    protected $response;
    protected $form;

    protected $attachment;

    public function __construct ( $request = null ) {

        $this->response         = new AOModelResponse();
        $this->form             = new AOFormMedia();

        if ( ! empty( $request['method'] ) && method_exists( $this, $request['method'], ) ){
            $this->{$request['method']}( $request );
        }

    }

    public function getResponse ( ) {

        return $this->response;

    }

    public function uploadMedia ( $request = null ) {

        /**
         * When assembleFileChunks() returns true, we will have a fully
         * assembled $_FILES array to play with.
         */
        $wp_response = $this->assembleFileChunks();

        if ( $wp_response === 'continue' ) {
            return;
        }

        if ( is_wp_error( $wp_response ) ) {

            $this->response->result = 0;
            $this->response->messages = $wp_response->get_error_messages();

        }
        else {

            $image = get_post( $wp_response );

            $this->response->result = 1;
            $this->response->data = $image;

        }

    }

    protected function assembleFileChunks ( ) {

        try {

            /**
                $_FILES['file']['name']
                $_FILES['file']['tmp_name']
                $_POST['dzChunkIndex']
                $_POST['dzTotalChunkCount']
                $_POST['dzUuid']
                $_POST['dzFilename']
            */

            // pr([ $_FILES, $_POST ]);

            /**
             * First create the target upload folder within WP's current upload folder.
             * This will look like ../wp-content/uploads/tmp/uuid
             */
            $this->target_path  = null;
            $upload_dir   = wp_upload_dir();
            if ( ! empty( $upload_dir['basedir'] ) ) {
                $this->target_path = $upload_dir['basedir'] . '/tmp/' . $_POST['dzUuid'] . '/';
                if ( ! file_exists( $this->target_path ) ) {
                    wp_mkdir_p( $this->target_path );
                }
            }

            $tmp_name = $_FILES['file']['tmp_name'];
            $filename = $_FILES['file']['name'];
            $target_file = $this->target_path.$filename;
            $num = intval( $_POST['dzChunkIndex'] );
            $num_chunks = intval( $_POST['dzTotalChunkCount'] );

            move_uploaded_file( $tmp_name, $target_file.$num );

            $this->response->messages[] = 'Chunk #' . $num . ' uploaded successfully';

            /** Count the number of uploaded chunks. */
            $chunksUploaded = count( glob( $this->target_path . '*') );

            /** When this triggers - that means your chunks are all uploaded. */
            if ( $chunksUploaded === $num_chunks ) {

                $this->response->messages[] = 'All ' . $num_chunks . ' chunks uploaded successfully';

                /** Now reassemble the chunks into a file. */
                for ( $i = 0; $i < $num_chunks; $i++ ) {

                    $file = fopen( $target_file.$i, 'rb' );
                    $buff = fread( $file, filesize( $target_file.$i ) );
                    fclose( $file );

                    $final = fopen( $target_file, 'ab' );
                    $write = fwrite( $final, $buff );
                    fclose( $final );

                    unlink( $target_file.$i );

                }

                $this->response->messages[] = 'All chunks combined successfully';

                $new_target = wp_upload_dir()['path'] . '/' . $filename;

                $unique_target = $this->getUniqueFilename( $new_target );

                $this->response->messages[] = ( rename( $target_file, $unique_target ) )
                    ? $target_file . ' moved to ' . $new_target
                    : $target_file . ' was NOT moved to ' . $unique_target;

                $this->response->messages[] = ( rmdir( $this->target_path ) )
                    ? $this->target_path . ' removed.'
                    : $this->target_path . ' was not removed.';

                $this->attachment = [
                    'guid'=> wp_upload_dir()['url'] . '/' . pathinfo( $unique_target, PATHINFO_BASENAME ),
                    'post_mime_type' => mime_content_type( $unique_target ),
                    'post_title' => '',
                    'post_content' => '',
                    'post_status' => 'inherit'
                ];

                return $this->saveFileAsAttachment ( $unique_target );

            }
            else {

                $this->response->result = 1;
                return 'continue';

            }

        }
        catch ( Exception | Error $e ) {

            $this->response->result = 0;
            $this->response->messages = [ $e->getMessage() ];
            return 'continue';

        }

    }

    protected function saveFileAsAttachment ( $filename ) {

        $wp_response = wp_insert_attachment( $this->attachment, $filename, 0, true );

        if ( is_wp_error( $wp_response ) ) { return $wp_response; }

        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        $attach_data = wp_generate_attachment_metadata( $wp_response, $filename );
        wp_update_attachment_metadata( $wp_response, $attach_data );

        $this->response->element = $attach_data;

        /** Update the image post with correct metadata. */
        $post = get_post( $wp_response );
        $this->setUploadPostData( $post, $attach_data, $filename );
        $post_response = wp_update_post( $post, true );

        $this->response->messages[] = ( is_wp_error( $post_response ) )
            ? $post_response->get_error_messages()
            : $post_response;

        return $wp_response;

    }

    public function setMediaCategories ( $id, $cats ) {

        /** Set categories for this media post. These are WP_Term objects. */

        $categories = [];
        $media_categories = ( is_string( $cats ) )
            ? explode(',', $cats )
            : $cats;

        foreach ( $media_categories as $slug ) {

            $wp_term = get_category_by_slug( trim( $slug ) );

            if ( $wp_term instanceof \WP_Term ) {

                $categories[] = $wp_term->term_id;

            }

        }

        wp_set_post_categories( $id, $categories );

    }

    public function getUniqueFilename ( $target_filename ) {

        $filepath           = pathinfo( $target_filename, PATHINFO_DIRNAME );
        $original_filename  = pathinfo( $target_filename,PATHINFO_FILENAME );
        $extension          = pathinfo( $target_filename, PATHINFO_EXTENSION );
        $new_filename       = $original_filename . '.' . $extension;

        $i = 1;
        while( file_exists( $filepath . '/' . $new_filename ) )
        {
            $new_filename = $original_filename . '_' . $i . '.' . $extension;
            $i++;
        }

        return $filepath . '/' . $new_filename;

    }

    protected function setUploadPostData ( & $post, $attach_data, $filename ) {

        $categories = '';

        if ( strstr( $post->post_mime_type, 'image') && isset( $attach_data['image_meta'] ) ) {

            $post->post_name    = $attach_data['image_meta']['title'];
            $post->post_title   = $attach_data['image_meta']['title'];
            $post->post_excerpt = $attach_data['image_meta']['caption'];

            $categories = 'ao-media, ao-image, ao-gallery';

        }
        elseif ( strstr( $post->post_mime_type, 'pdf') ) {

            $pdf = Zend_Pdf::load( $filename );

            $post->post_name    = $pdf->properties['Title'];
            $post->post_title   = $pdf->properties['Title'];
            $post->post_excerpt = $pdf->properties['Title'];

            $categories = 'ao-media, ao-document';

        }
        elseif ( strstr( $post->post_mime_type, 'video' ) ) {

            $this->saveVideoThumbnail( $filename );

            $categories = 'ao-media, ao-video';

        }

        /** Set categories for this image. */
        $this->setMediaCategories ( $post->ID, $categories );

    }

    public function saveVideoThumbnail ( $video_filename, $timecode = '00:00:05.000' ) {

        /**
         * At this point the video has already been saved and now lives
         * in the proper upload directory. This is the $video_filename
         * which includes the full internal path to the file.
         */

        /** Create the name of the new video thumbnail IN the uploads folder. */
        $thumbnail  = pathinfo( $video_filename, PATHINFO_DIRNAME ) . '/' .
            pathinfo( $video_filename, PATHINFO_FILENAME ) . '_thumbnail.jpg';
        $ffmpeg     = plugin_dir_path( __FILE__ ) . 'inc/ffmpeg/ffmpeg';
        $response   = shell_exec("$ffmpeg -i $video_filename -ss $timecode -vframes 1 $thumbnail 2>&1");

    }

    public function getImageGallery ( $request ) {

        /** Returns a WP_Term object. */
        $wp_term_obj = get_category_by_slug( 'ao-gallery' );

        $args = [
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'category' => $wp_term_obj->term_id,    // Must have the 'gallery' category.
            'numberposts' => -1,
            // 'post_status' => null,
            // 'post_parent' => null, // any parent
        ];

        $posts = get_posts( $args ); // Array of WP_Post objects

        $response = [];
        foreach( $posts as $WP_Post ) {
            $post = (array) $WP_Post;
            $post['thumbnail'] = wp_get_attachment_image_url( $WP_Post->ID, 'large' );
            $response[] = $post;
        }

        /**
         WP_Post Object (
            [ID] => 9
            [post_author] => 1
            [post_date] => 2022-07-02 16:50:10
            [post_date_gmt] => 2022-07-02 16:50:10
            [post_content] =>
            [post_title] => Everything-I-Need
            [post_excerpt] =>
            [post_status] => inherit
            [comment_status] => open
            [ping_status] => closed
            [post_password] =>
            [post_name] => everything-i-need
            [to_ping] =>
            [pinged] =>
            [post_modified] => 2022-07-02 16:50:10
            [post_modified_gmt] => 2022-07-02 16:50:10
            [post_content_filtered] =>
            [post_parent] => 0
            [guid] => https://3ao-beaudev.com/wp-content/uploads/2022/07/Everything-I-Need.jpg
            [menu_order] => 0
            [post_type] => attachment
            [post_mime_type] => image/jpeg
            [comment_count] => 0
            [filter] => raw
        )
        */

        $this->response->result = 1;
        $this->response->data = $response;

    }

    public function getDocumentGallery ( $request ) {

        /** Returns a WP_Term object. */
        $wp_term_obj = get_category_by_slug( 'ao-document' );

        $args = [
            'post_type' => 'attachment',
            'post_mime_type' => 'application/pdf',
            'category' => $wp_term_obj->term_id,    // Must have the 'ao-document' category.
            'numberposts' => -1,
            //'post_status' => null,
            //'post_parent' => null, // any parent
        ];

        $data = [];
        $wp_posts = get_posts( $args ); // Array of WP_Post objects
        foreach( $wp_posts as $wp_post ){
            $data[] = [
                'data' => $wp_post,
                'element' => wp_get_attachment_metadata( $wp_post->ID )
            ];
        }

        $this->response->result = 1;
        $this->response->data = $data;

    }

    public function getMediaGallery ( $request ) {

        /** Returns a WP_Term object. */
        $wp_term_obj = get_category_by_slug( 'ao-video' );

        $args = [
            'post_type' => 'attachment',
            'post_mime_type' => 'video',
            'category' => $wp_term_obj->term_id,    // Must have the 'ao-video' category.
            'numberposts' => -1,
            //'post_status' => null,
            //'post_parent' => null,    // any parent
        ];

        $data = [];
        $wp_posts = get_posts( $args );     // Array of WP_Post objects
        foreach ( $wp_posts as $wp_post ) {
            $data[] = [
                'data' => $wp_post,
                'element' => wp_get_attachment_metadata( $wp_post->ID )
            ];
        }

        $this->response->result = 1;
        $this->response->data = $data;

    }

    public function deleteMedia ( $request ) {

        $response = wp_delete_post( $request['id'], true );

        if ( is_wp_error( $response ) ) {

            $this->response->result = 0;
            $this->response->messages = $response->get_error_messages();

        }
        else {

            $this->response->result = 1;

        }
    }

    public function getFormData ( $request ) {

        $this->response->result = 1;
        $this->response->form = $this->form->getName();
        $this->response->data = $this->getMediaFormData( $request['id'] );
        $this->response->element = wp_get_attachment_metadata( $request['id'] );

    }

    public function getMediaFormData ( $id ) {

        $post   = get_post( $id );  // returns a WP_Post object
        $alt    = trim( strip_tags( get_post_meta( $id, '_wp_attachment_image_alt', true ) ) );

        $cats   = wp_get_post_categories( $id );
        $categories = [];
        foreach ( $cats as $cat_id ) {
            $categories[] = get_term( $cat_id )->slug;
        }

        /**
            [post_content] => Jenny's description.
            [post_title] => Jenny's title
            [post_excerpt] => Jenny's caption
        */

        return [
            'media_id'          => $id,
            'media_title'       => $post->post_title,
            'media_caption'     => $post->post_excerpt,
            'media_description' => $post->post_content,
            'media_mime_type'   => $post->post_mime_type,
            'media_post_date'   => $post->post_date,
            'media_guid'        => $post->guid,
            'media_alt_text'    => $alt,
            'media_categories'  => $categories,
        ];

    }

    public function updateMedia ( $request ) {

        $imageData = $request['data'];

        if ( $this->form->isValid( $imageData ) ) {

            $data = $this->form->getValues();     // array

            update_post_meta( $data['media_id'], '_wp_attachment_image_alt', $data['media_alt_text'] );

            $postData = [
                'ID'            => $data['media_id'],
                'post_title'    => $data['media_title'],
                'post_excerpt'  => $data['media_caption'],
                'post_content'  => $data['media_description'],
            ];

            $response = wp_update_post( $postData, true );

            $this->setMediaCategories( $data['media_id'], $data['media_categories']);

            if ( is_wp_error( $response ) ) {

                $this->response->result = 0;
                $this->response->form = $this->form->getName();
                $this->response->setTextMessage( 'There was an error saving your data.', 'error' );
                $this->response->messages = $response->get_error_messages();

            }
            else {

                // Sends a success response //

                $this->response->result = 1;
                $this->response->form = $this->form->getName();
                $this->response->setTextMessage( 'Your image data has been saved.', 'success' );
                $this->response->messages = [];

            }


        } else {

            // Invalid Entry //

            $this->response->result = 0;
            $this->response->form = $this->form->getName();
            $this->response->setTextMessage( 'There are errors on your form.', 'error' );
            $this->response->messages = $this->form->getMessages();

        }

    }


}