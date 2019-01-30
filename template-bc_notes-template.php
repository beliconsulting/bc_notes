<?php
/*
 * Template Name: Notes
*/

get_header(); ?>

<div id="page" class="single">
	<div class="content">
		<article class="article">
			<div id="content_box" >
				<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
					<div id="post-<?php the_ID(); ?>" <?php post_class('g post'); ?>>
						<div class="single_page single_post clear">
							<div id="content" class="post-single-content box mark-links">
							<header>
								<h1 class="title"><?php the_title(); ?></h1>
							</header>
								<?php the_content(); ?>                                    

  <?php
    $page_id = get_the_ID();
    $comments = get_comments();
    echo "<div class='bc_notes'>";
    echo "<ul>";
    foreach($comments as $comment){
      if($comment->comment_post_ID == $page_id){
        $comment_meta = get_comment_meta($comment->comment_ID);
        /*
        echo "<pre>";
        print_r($comment_meta);
        echo "</pre>";
         */
        echo "<li id='bc_notes_li'>";
          if($comment->comment_author_url != ""){
            echo "<a href='$comment->comment_author_url'>";
          }else{
            echo "<a href='#'>";
          }
          echo "<h2>".$comment_meta['title']['0']."</h2>";
          echo "<p>$comment->comment_content</p>";
          echo "<p>$comment->comment_author</p>";
        echo "</a>";
      echo "</li>";
      }
    }
    echo "</ul>";
    echo "</div>";

$args = array(
        'label_submit'=>'Los',
        'title_reply'=>'<h4>Gib eine Anzeige auf!</h4>',
);
comment_form($args);

/*
echo "<p style='height:200px;'> <pre>";
print_r($comments);
echo "</pre>";
 */
?>

							</div>
						</div>
					</div>
				<?php endwhile; ?>
			</div>
		</article>
		<?php get_sidebar(); ?>
		</div>
		</div>
		<?php get_footer(); ?>
