<?php

    // get working years for footer
    $cur_year = date( 'Y', time() );
    $started_work_year = '2017';
    $years_of_work = ( $cur_year !== $started_work_year ) ? $started_work_year . '-' . $cur_year : $started_work_year;
?>
       
        </div>
       
        <footer class="footer">
            <div class="container text-center">
                <span class="text-muted">&#169; <?=$years_of_work?> EduCoin, Education is your coin.</span>
            </div>
        </footer>
       
        <?php wp_footer(); ?>
  
    </body>
</html>