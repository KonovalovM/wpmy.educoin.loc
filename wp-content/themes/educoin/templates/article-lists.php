<?php /* Template Name: Article lists */ ?>


<?php 

    $args = [
        'post_type' => 'page', 
        'post_parent' => get_the_id(), 
        'orderby' => 'name', 
        'order' => 'ASC',
    ];

    // get articles
    $articles_query_res = new WP_Query( $args );
    $articles = $articles_query_res->posts;
?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?=get_the_title()?></h2>
            <br>

                  <?php 

                      if ( !empty( $articles ) ) { 
                          $qnty_articles = count( $articles );
                          $qnty_items_in_row = 3;
                          $i = 1;
                          $j = 1;
                          $subblocks = '';
                          $wrapper = '<div class="card-deck">[content]</div>';
                          foreach ( $articles as $index => $article ) {
    
                              // generate URL for analytics system
                              $params = [
                                  'task' => 'user_visited_page',
                                  'object_id' => $article->ID,
                              ];
                              $analytics_page_url = edc_generate_url_analytics( get_permalink( $article->ID ), $params );

                              $subblocks .= '<div class="card text-center mt-3 mb-3"><div class="card-body">';
                              $subblocks .= '<h4 class="card-title">' . $article->post_title . '</h4>';
                              $subblocks .= '<a href="' . $analytics_page_url . '" class="btn btn-primary">' . __( 'Перейти', TEXTDOMAIN ) . '</a>';
                              $subblocks .= '</div></div>';

                              if ( ( $i === $qnty_items_in_row ) || ( $j === $qnty_articles ) ) {

                                  // print the nodes
                                  echo str_replace( '[content]', $subblocks, $wrapper );

                                  $i = 0;
                                  $subblocks = '';
                              }
                              $i++;
                              $j++;
                          }
                      } else {
                  ?>
                          <br>
                          <p class="text-center"><?=__( 'Список статей пока пуст.', TEXTDOMAIN )?></p>
                  <?php  
                      }
                  ?>

        </div>
    </div>
        
</div>


<?php get_footer() ?>