var $ = jQuery.noConflict();
$(function() {
  $(document).ready(function() {
    $('.wpx-filter-type').on('change', function(){
        var catFilter = $(this).val();
        if( catFilter != '' ){
            document.location.href = catFilter;
        }
    });

    var dateFormat = formatDate('F j, Y');
    // define close from and to datepicker
    var option = {
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 1,
        minDate : 0,
    };
    var from = $(".datepicker.from") .datepicker(option);
    var to = $(".datepicker.to").datepicker(option);

    from.on("change", function() {
      var box = $(this).closest('fieldset');
      $(box).find(to).datepicker("option", "minDate", getDate(this, dateFormat));
    });

    to.on("change", function() {
      var box = $(this).closest('fieldset');
      $(box).find(from).datepicker("option", "maxDate", getDate(this, dateFormat));
    });

    $('input.timepicker').timepicker({ 'timeFormat': 'H:i', 'step': 60 });

    // clone range input fields
    $(document).on('click', '.plusel', function(e) {
      e.preventDefault();
      var elm = $(this).siblings('fieldset').last();
      var cl  = elm.clone(true).insertAfter(elm);

      //check if first elem
      if(cl.data('idx') == 0) {
          cl.append( '<a href="#" class="rmel">-</a>' );
      }

      cl.data('idx', elm.data('idx')+1);

      var fname = elm.children('.from').attr("name").replace(/\d+/, cl.data('idx'));
      var ftname = elm.children('.fromt').attr("name").replace(/\d+/, cl.data('idx'));

      var tname = elm.children('.to').attr("name").replace(/\d+/, cl.data('idx'));
      var ttname = elm.children('.tot').attr("name").replace(/\d+/, cl.data('idx'));

      $(cl).children('.from').attr("name", fname);
      $(cl).children('.to').attr("name", tname);

      $(cl).children('.fromt').attr("name", ftname);
      $(cl).children('.tot').attr("name", ttname);


      cl.find('input.datepicker').each(function() {
          // remove still present related DOM objects
          $(this).val('');
          $(this).removeAttr('id');
          $(this)
            .siblings('.ui-datepicker-trigger,.ui-datepicker-apply')
            .remove();
          // remove datepicker object and detach events
          $(this)
            .removeClass('hasDatepicker')
            .removeData('datepicker')
            .unbind()
            .datepicker(option);
      });

      cl.find('input.ui-timepicker-input').each(function() {
          // remove still present related DOM objects
          $(this).val('');
          // $(this).removeAttr('id');
          $(this)
          .timepicker('remove');
          //   .siblings('.ui-datepicker-trigger,.ui-datepicker-apply')
          //   .remove();
          // remove datepicker object and detach events
          $(this)
            .timepicker({ 'timeFormat': 'H:i', 'step': 60});
      });
    });

    //remove range fields
    $(document).on('click', '.rmel', function(e) {
        e.preventDefault();
        var elm = $(this).parent('fieldset');
        elm.remove();
    });

  });

  function getDate(element, dateFormat) {
      var date;
      //console.log($.datepicker.parseDate(dateFormat, element.value));
      try {
        date = element.value;
      } catch (error) {
        date = null;
      }
      return date;
  }


  function formatDate( $sFormat ) {
      switch( $sFormat ) {
          //Predefined WP date formats
          case 'F j, Y':
              return( 'MM dd, yy' );
              break;
          case 'Y/m/d':
              return( 'yy/mm/dd' );
              break;
          case 'm/d/Y':
              return( 'mm/dd/yy' );
              break;
          case 'd/m/Y':
              return( 'dd/mm/yy' );
              break;
      }
  }
});
