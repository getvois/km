
$(document).ready(function(){
    <!-- MODAL TREE STRUCTURE -->

    $("li.parent-list ul").hide(); //hide the child lists
    $("li.parent-list i").click(function () {
        $(this).toggleClass('glyphicon-chevron-down glyphicon-chevron-up'); // toggle the font-awesome icon class on click
        $(this).next().next().next().next("ul").toggle(); // toggle the visibility of the child list on click

        //body top margin
            var $cityPicker = $(this).closest(".city-picker");
            var $height = parseInt($cityPicker.height());
            $height += parseInt($cityPicker.css("padding-top").replace("px", ""));
            $height += parseInt($cityPicker.css("padding-bottom").replace("px", ""));

            $("body").css("margin-top", $height);
            $cityPicker.css("margin-top", 0);

    });

    <!--MODAL MULTI SELECT FUNCTIONALITY -->

// check-uncheck all
    $(".all").off().change(function () {
        $(this).closest(".city-picker").find('.city-list').find(":checkbox").prop("checked", 0).eq(1).change();
        $(this).closest(".city-filters").find('.city-search').val("").keyup();
        $(this).prop("checked", 0);
    });


// parent/child check-uncheck all
    $('input[class=parent]').on('change', function () {
        $(this).closest('ul li').find(':checkbox').prop('checked', this.checked);
    });
});


