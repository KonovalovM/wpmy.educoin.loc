<?php get_header() ?>

<?php get_sidebar( 'left' ); ?>


<hr>

<h2>Урок: "<?php echo get_the_title(); ?>"</h2>

<p><?php echo get_field( 'edc_lesson_description', get_the_id() ); ?></p>
---------------------
<br>

<p><?php echo get_field( 'edc_lesson_video', get_the_id() ); ?></p>
---------------------
<br>

<?php echo edc_get_post_content( get_the_id() ); ?>


<?php get_footer() ?>