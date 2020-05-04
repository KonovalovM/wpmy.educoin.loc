<?php

    $categ = get_term( edc_get_current_course_id() );

    $books_args = [
        'post_type' => 'book',
        $categ->taxonomy => $categ->slug,
    ];
    $books_query = new WP_Query( $books_args );

    // sorting items
    $books = $books_query->posts;

    foreach ( $books as $book ) {
        $order = get_field( 'edc_book_order_num', $book->ID );
        $book->order = $order ? $order : 9999;
        
        $book->book_path = get_field( 'edc_book_path', $book->ID );
        $book->img_path = get_the_post_thumbnail_url( get_the_ID(), 'full' );
    }
    usort( $books, 'edc_order_title_arr_sort' );

?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>


<h2>Книги</h2>

<?php

    foreach ( $books as $book ) {
?>
        <a href="<?php echo get_permalink( $book->ID )?>"><?php echo $book->post_title; ?></a>
        <br>
<?php
    }
?>


        </div>
    </div>
</div>
















<?php get_footer() ?>