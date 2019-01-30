<?php
/*
Plugin Name: BC Notes
Version: 1.0
Plugin URI: https://beli-consulting.com
Description: Notes Plugin
Author: Björn Becker
Author URI: https://beli-consulting.com
*/

require_once dirname( __FILE__ ) .'/templates.php';

// Add style
add_action('wp_enqueue_scripts', 'callback_for_setting_up_scripts');
function callback_for_setting_up_scripts() {
    wp_register_style( 'namespace', '/wp-content/plugins/bc_notes/style.css' );
    wp_enqueue_style( 'namespace' );
}

// Add custom meta (durations) fields to the default comment form
// Default comment form includes name, email and URL
// Default comment form elements are hidden when user is logged in
add_filter('comment_form_default_fields','custom_fields');
function custom_fields($fields) {

                $commenter = wp_get_current_commenter();
                $req = get_option( 'require_name_email' );
                $aria_req = ( $req ? " aria-required='true'" : '' );

                $fields[ 'author' ] = '<p class="comment-form-author">'.
                        '<label for="author">' . __( 'Name' ) . '</label>'.
                        ( $req ? '<span class="required">*</span>' : '' ).
                        '<input id="author" name="author" type="text" value="'. esc_attr( $commenter['comment_author'] ) . 
                        '" size="30" tabindex="1"' . $aria_req . ' /></p>';
                
                $fields[ 'email' ] = '<p class="comment-form-email">'.
                        '<label for="email">' . __( 'Email' ) . '</label>'.
                        ( $req ? '<span class="required">*</span>' : '' ).
                        '<input id="email" name="email" type="text" value="'. esc_attr( $commenter['comment_author_email'] ) . 
                        '" size="30"  tabindex="2"' . $aria_req . ' /></p>';
                                        
                $fields[ 'url' ] = '<p class="comment-form-url">'.
                        '<label for="url">' . __( 'Website' ) . '</label>'.
                        '<input id="url" name="url" type="text" value="'. esc_attr( $commenter['comment_author_url'] ) . 
                        '" size="30"  tabindex="3" /></p>';

                $fields[ 'phone' ] = '<p class="comment-form-phone">'.
                        '<label for="phone">' . __( 'Phone' ) . '</label>'.
                        '<input id="phone" name="phone" type="text" size="30"  tabindex="4" /></p>';

        return $fields;
}

// Add fields after default fields above the comment box, always visible
add_action( 'comment_form_logged_in_after', 'additional_fields' );
add_action( 'comment_form_after_fields', 'additional_fields' );

function additional_fields () {
        echo '<p class="comment-form-title">'.
        '<label for="title">' . __( 'Titel' ) . '</label>'.
        '<input id="title" name="title" type="text" size="30"  tabindex="5" /></p>';

        echo '<p class="comment-form-duration">'.
        '<label for="duration">'. __('Dauer') . '<span class="required">*</span></label>
        <span class="commentdurationbox">';
        
        echo '<span class="commentduration"><select name="duration" id="duration">
          <option value="5">5 Tage</option>
          <option value="10">10 Tage</option>
          <option value="30">30 Tage</option></select></span>';

        echo'</span></p>';

}

// Save the comment meta data along with comment
add_action( 'comment_post', 'save_comment_meta_data' );
function save_comment_meta_data( $comment_id ) {
        if ( ( isset( $_POST['phone'] ) ) && ( $_POST['phone'] != '') )
        $phone = wp_filter_nohtml_kses($_POST['phone']);
        add_comment_meta( $comment_id, 'phone', $phone );

        if ( ( isset( $_POST['title'] ) ) && ( $_POST['title'] != '') )
        $title = wp_filter_nohtml_kses($_POST['title']);
        add_comment_meta( $comment_id, 'title', $title );

        if ( ( isset( $_POST['duration'] ) ) && ( $_POST['duration'] != '') )
        $duration = wp_filter_nohtml_kses($_POST['duration']);
        add_comment_meta( $comment_id, 'duration', $duration );
}


// Add the filter to check if the comment meta data has been filled or not
add_filter( 'preprocess_comment', 'verify_comment_meta_data' );
function verify_comment_meta_data( $commentdata ) {
        if ( ! isset( $_POST['duration'] ) )
        wp_die( __( 'Error: You did not add your duration. Hit the BACK button of your Web browser and resubmit your comment with duration.' ) );
        return $commentdata;
}

//Add an edit option in comment edit screen  
add_action( 'add_meta_boxes_comment', 'extend_comment_add_meta_box' );
function extend_comment_add_meta_box() {
    add_meta_box( 'title', __( 'Comment Metadata - Extend Comment' ), 'extend_comment_meta_box', 'comment', 'normal', 'high' );
}
 
function extend_comment_meta_box ( $comment ) {
    $phone = get_comment_meta( $comment->comment_ID, 'phone', true );
    $title = get_comment_meta( $comment->comment_ID, 'title', true );
    $duration = get_comment_meta( $comment->comment_ID, 'duration', true );
    wp_nonce_field( 'extend_comment_update', 'extend_comment_update', false );
    ?>
    <p>
        <label for="phone"><?php _e( 'Phone' ); ?></label>
        <input type="text" name="phone" value="<?php echo esc_attr( $phone ); ?>" class="widefat" />
    </p>
    <p>
        <label for="title"><?php _e( 'Comment Title' ); ?></label>
        <input type="text" name="title" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
    </p>
    <p>
        <label for="duration"><?php _e( 'Duration: ' ); ?></label>
                        <span class="commentdurationbox">
                        <?php for( $i=1; $i <= 5; $i++ ) {
                                echo '<span class="commentduration"><input type="radio" name="duration" id="duration" value="'. $i .'"';
                                if ( $duration == $i ) echo ' checked="checked"';
                                echo ' />'. $i .' </span>'; 
                                }
                        ?>
                        </span>
    </p>
    <?php
}

// Update comment meta data from comment edit screen 
add_action( 'edit_comment', 'extend_comment_edit_metafields' );
function extend_comment_edit_metafields( $comment_id ) {
    if( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) return;

        if ( ( isset( $_POST['phone'] ) ) && ( $_POST['phone'] != '') ) : 
        $phone = wp_filter_nohtml_kses($_POST['phone']);
        update_comment_meta( $comment_id, 'phone', $phone );
        else :
        delete_comment_meta( $comment_id, 'phone');
        endif;
                
        if ( ( isset( $_POST['title'] ) ) && ( $_POST['title'] != '') ):
        $title = wp_filter_nohtml_kses($_POST['title']);
        update_comment_meta( $comment_id, 'title', $title );
        else :
        delete_comment_meta( $comment_id, 'title');
        endif;

        if ( ( isset( $_POST['duration'] ) ) && ( $_POST['duration'] != '') ):
        $duration = wp_filter_nohtml_kses($_POST['duration']);
        update_comment_meta( $comment_id, 'duration', $duration );
        else :
        delete_comment_meta( $comment_id, 'duration');
        endif;
        
}

// Add the comment meta (saved earlier) to the comment text 
// You can also output the comment meta values directly in comments template  
add_filter( 'comment_text', 'modify_comment');
function modify_comment( $text ){

        $plugin_url_path = WP_PLUGIN_URL;

        if( $commenttitle = get_comment_meta( get_comment_ID(), 'title', true ) ) {
                $commenttitle = '<strong>' . esc_attr( $commenttitle ) . '</strong><br/>';
                $text = $commenttitle . $text;
        } 

        if( $commentduration = get_comment_meta( get_comment_ID(), 'duration', true ) ) {
                $commentduration = '<p class="comment-duration">
                <br/>Duration: <strong>'. $commentduration .'</strong></p>';
                $text = $text . $commentduration;
                return $text;           
        } else {
                return $text;           
        }        
}
