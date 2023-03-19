$( function () {
  if ( $( '#dynamicmodel-source_id' ).val() === '' ) {
    let source_id = $( '.export_step_2_title' ).data( 'source_id' );
    $( '#dynamicmodel-source_id' ).val( source_id );
  }

  function set_url() {
    const source_id = $( '.export_step_2_title' ).data( 'source_id' );
    let url = `/exports/step_4?source_id=${source_id}` +
      $( '#dynamicmodel-comparisons input:checked' ).map( ( i, el ) => `&comparisons[]=${$( el ).val()}` ).get().join( '' );
    url = `${url}&profile=${$( '#dynamicmodel-profile' ).val()}`;

    if ($('#dynamicmodel-is_new').is(':checked')) {
      url = `${url}&is_new=1`;
    }

    $( 'a.js-download' ).prop( 'href', url );
  }


  set_url();

  $( 'body' ).on( 'click', '.js-download', function () {
    set_url();
  } )

  $( 'body' ).on( 'click', '#dynamicmodel-use_previous_saved', function () {
    if ( this.checked ) {
      $( '#dynamicmodel-ignore_step_3' ).parent().show();
    } else {
      $( '#dynamicmodel-ignore_step_3' ).parent().hide();

    }
  } );

  $( 'body' ).on( 'click', '#dynamicmodel-ignore_step_3', function () {

    set_url();

    if ( this.checked ) {
      $( '.js-next' ).hide();
      $( 'a.js-download' ).show();
    } else {
      $( '.js-next' ).show();
      $( 'a.js-download' ).hide();
    }
  } )

} )