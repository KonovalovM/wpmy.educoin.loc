<?php


/**
* output the pdf
*/
function edcpdf_output_pdf( $query ) {

  $pdf = sanitize_text_field( get_query_var( 'pdf' ) );

  if( $pdf ) {

	  require_once  realpath(__DIR__ . '/..') . '/inc/vendors/pdf/autoload.php';

      // page orientation
        $format = apply_filters( 'edcpdf_pdf_format', 'A4' ).'-L';

      $mpdf_config = apply_filters('edcpdf_mpdf_config',[
          'format'            => $format,
      ]);

      // creating and setting the pdf
      $mpdf = new \Mpdf\Mpdf( $mpdf_config );

      $mpdf->WriteHTML( edcpdf_get_template( 'certificat-view' ) );

//get user ID
        $cert_uid = sanitize_text_field( $_GET['uid'] );

        $uidcert = CACHE_DIR . '/' . $_GET['uid'] . '.pdf';
//download for view
        $mpdf->Output( certificate.'.pdf', 'I' );
//download to server
        $mpdf->Output ( $uidcert, 'F' );

      exit;
  }

}

add_action( 'wp', 'edcpdf_output_pdf' );

/**
* connect a template files
*/
function edcpdf_get_template( $template_name ) {

    $template = new EDCPDF_Template_Loader;

    $template->get_template_part( 'certificat', 'view' );
    return ob_get_clean();
}

/**
* set query_vars
*/
function edcpdf_set_query_vars( $query_vars ) {

  $query_vars[] = 'pdf';

  return $query_vars;
}

add_filter( 'query_vars', 'edcpdf_set_query_vars' );

