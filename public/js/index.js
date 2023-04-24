
/**
 * After the document successfully loaded,if users try to delete the stocks he
 * added by him previously then this ajax call used.
 */
$(document).ready(function() {
  $(".delete").click(function() {
    var stock = $(this).attr("id");
      $.ajax({
        url: "/deletestock",
        method: "POST",
        data:
        {
          stockId: stock,
        },
        datatype: "JSON",
        success: function (data)
        {
          var success = jQuery.parseJSON(data)["success"];
          if (success) {
            update();
          }
        },
      });
  });

  $(".edit").click(function() {
    var id = $(this).attr("id");
    $(".name#"+id).attr("disabled",false);
    $(".price#"+id).attr("disabled",false);
  });
});


/**
 * This function is to load stocks.
 */
function update()
{
  $.ajax({
      url: "/update",
      method: "POST",
      data:
      {
        url: window.location.pathname,
      },
      success: function (data)
      {
        $(".stock-table").html(data);
      }
    });
}
