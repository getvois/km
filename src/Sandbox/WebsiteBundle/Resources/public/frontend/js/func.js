function getFilter(container){
    if(!container) container = ".travelbase_items:visible";

    var $type = [];
    if($("#edit-only-flights").is(":checked")) $type.push(4);
    if($("#edit-only-hotel").is(":checked")) $type.push(6);
    if($("#edit-flight-and-hotel").is(":checked")) $type.push(1);
    if($("#edit-flight-and-hotel-unknown").is(":checked")) $type.push(2);

    var $dataFilter = $(container).data('filter-type');
    if($dataFilter){
        $type = $dataFilter;
    }

    var $form = $('#travelbase-form');

    var $field = $form.find('input[name="order_field"]').val();

    var $direction = $form.find('input[name="order_direction"]');

    var  $directionVal = $direction.val();
    //var  $directionVal = "";
    //if($direction.val() == 'asc') $directionVal = 'desc';
    //else $directionVal = 'asc';
    //
    //$direction.val($directionVal);

    var $destination_country = [];
    var $destination_city = [];
    var $departure_country = [];
    var $departure = [];

    //var $dropdown = $("#dropdown-destination");
    //if($dropdown.find(".city-list").find('input[type=checkbox]:checked').length == 0){
    //    $dropdown.find(".city-list").find('input[type=checkbox]').each(function () {
    //        if($(this).data('type') == 'country'){
    //            $destination_country.push($(this).val());
    //        }else if($(this).data('type') == 'city'){
    //            $destination_city.push($(this).val());
    //        }
    //    });
    //}else{
    //    $dropdown.find(".city-list").find('input[type=checkbox]').each(function () {
    //        if($(this).is(":checked")){
    //            if($(this).data('type') == 'country'){
    //                $destination_country.push($(this).val());
    //            }else if($(this).data('type') == 'city'){
    //                $destination_city.push($(this).val());
    //            }
    //        }
    //    });
    //}

    $destData = $('#destination-selected').select2("data");
    for(var i=0;i<$destData.length; i++){
        var $id = $destData[i].id;
        if($id.toString().indexOf("_") != -1){
            //country
            $destination_country.push($id.replace("_", ""));
        }else{
            //city
            $destination_city.push($id);
        }
    }




    //$("#dropdown-departure").find(".city-list").find('input[type=checkbox]').each(function () {
    //    if($(this).is(":checked")){
    //        if($(this).data('type') == 'country'){
    //            $departure_country.push($(this).val());
    //        }else if($(this).data('type') == 'city'){
    //            $departure.push($(this).val());
    //        }
    //    }
    //});

    $depData = $('#departure-selected').select2("data");
    for(var i=0;i<$depData.length; i++){
        var $id = $depData[i].id;
        if($id.toString().indexOf("_") != -1){
            //country
            $departure_country.push($id.replace("_", ""));
        }else{
            //city
            $departure.push($id);
        }
    }


    var $slider = $("#slider-range");
    var $sliderStopover = $("#slider-range-stopover");

    var $directFlight = 0;
    if($("#directFlight").is(":checked")) $directFlight = 1;

    var $filter = {
        departure_country: $departure_country,
        departure: $departure,
        company: [ $("#edit-companies").val() ],
        type: $type,
        price: {
            min: $slider.slider( "values", 0 ),
            max: $slider.slider( "values", 1 )
        },
        stopoverfrom:	$sliderStopover.slider( "values", 0 )/60 + ":00",
        stopoverto:	$sliderStopover.slider( "values", 1 )/60 + ":00",
        destination_country: $destination_country,
        destination_city: $destination_city,
        directFlights: $directFlight,
        limit: 10,
        offset: 0,
        orderField: $field,
        orderDirection: $directionVal
    };

    var $date_start = $("#edit-date-start-datepicker-popup-0").val();
    if($date_start){
        var $ds = $date_start;
        var $dates = $ds.substr(6, 4) + "-" + $ds.substr(3,2) + "-" + $ds.substr(0,2);
        $filter.date = { start: $dates };

        if($dpInterval == 30){
            var $month = parseInt($ds.substr(3, 2)) + 1;
            if($month.toString().length == 1) $month = "0" + $month;
            $filter.date.end = $ds.substr(6, 4) + "-" + $month + "-" + $ds.substr(0, 2);
        }else{
            $filter.date.end = $dates;
        }
    }

    var $date_end = $("#edit-date-end-datepicker-popup-0").val();
    if($date_end){
        var $d = $date_end;
        $filter.date.return = $d.substr(6, 4) + "-" + $d.substr(3, 2) + "-" + $d.substr(0, 2) ;
    }

    return $filter;
}

function loadMore(){
    var $filter = getFilter();

    var $tr = $(".travelbase_items:visible").find("> div > div");
    if($tr.length > 1){//1 because of header
        $filter.offset = $tr.length - 1 ;
    }

    $.post('/app_dev.php/api-filter/0', JSON.stringify($filter), function (responce) {
        var $table = $(".travelbase_items:visible").find('> div');
        $table.append(responce.html);

        if($table.find('> div').length - 1 >= responce.total){
            //hide load more btn
            $("button[id=loadMore]:visible").hide();
        }
    });

    var xhr = new XMLHttpRequest();
    xhr.open('POST', $api_url);
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (this.readyState == 4) {

            var $rows = '';
            var $data = JSON.parse(this.responseText);

            for(var i = 0; i< $data.items.length; i++){
                $rows += itemToRow($data.items[i]);
            }

            $rows += '<script>$(".my-popover").popover();</script><script></script>';
            var $table = $(".travelbase_items:visible").find('table');
            $table.append($rows);

            if($table.find('tr').length - 1 >= $data.total){
                //hide load more btn
                $("#loadMore").hide();
            }


        }
    };
    //xhr.send(JSON.stringify($filter));
}


var $urlCache = [];
/**
 * @return {boolean}
 */
function UrlExists(url)
{
    if($.inArray(url, $urlCache) != -1){
        return false;
    }


    var http = new XMLHttpRequest();
    http.open('HEAD', url, false);
    http.send();

    if(http.status==404)
        $urlCache.push(url);

    return http.status!=404;
}

var $prevDate = "";
var $dateCount = 0;
function itemToRow($item){

    if(!$item.info) $item.info = "";
    if(!$item.duration) $item.duration = "";
    var $hotel = "Flight";
    if($item.hotel != false) $hotel= $item.hotel.name;

    if($hotel == "Flight" && ($item.duration == "" || $item.duration == "1")){
        $item.duration = 'One way';
    }

    if($hotel == 'Flyg och första hotellnatt på Phuket' || $hotel == 'Flyg och första hotellnatt i Ao Nang'){
        $hotel = 'Hotel with 1 night stay only';
    }

    var $date = $item.date.substr(8, 2) + "." + $item.date.substr(5, 2) + "." + $item.date.substr(2, 2);

    var $class = '';
    if($prevDate == '')
        $prevDate = $date;
    $dateCount++;
    if($prevDate != $date) {
        $prevDate = $date;
        if($dateCount > 5)
            $class = 'day-sep';
        $dateCount = 0;
    }

    var $company = $item.company.name;

    if($company == 'SkyPicker') $class += " skypicker-toggle";

    if(UrlExists("/sites/all/modules/travelbase/img/" + $item.company.name.toLowerCase() + ".png"))
        $company = "<img src='/sites/all/modules/travelbase/img/" + $item.company.name.toLowerCase() + ".png' alt='" + $item.company.name + "' />";


    var $lastCol = "<a href='" + $item.link + "'>" + $company + "</a>";

    if($item.company.name == 'SkyPicker'){
        $lastCol = "";
        for(var i=0;i<$item.airline.length; i++){
            $lastCol += "<img src='/bundles/sandboxwebsite/img/airlines/"+$item.airline[i]+".gif' title="+$item.airline[i]+" alt="+$item.airline[i]+">" ;
            break;
            if(i < $item.airline.length - 1) $lastCol += " ";
        }
    }

    return "<tr class='" + $class + "' data-itemid='"+ $item.id +"' >" +
    "<td>" + $date + "</td>" +
        //"<td>" + $company + "</td>" +
    "<td>" + $item.departure.cityNameFi + "</td>" +
    "<td>" + $item.destination.cityNameFi + "</td>" +
    "<td><a href='#' onclick='return false;' class='my-popover' data-toggle='popover' title='"+$hotel+"' data-content='"+$item.info+"' >" + $hotel + "</a></td>" +
        //"<td>" + $item.info + "</td>" +
    "<td>" + $item.duration + "</td>" +
    "<td>" + Math.round($item.price) + "</td>" +
    "<td>"+$lastCol+"</td>" +
    "</tr>";
}

function unCheckAll(a, e){
    e = e || window.event;
    e.preventDefault();
    e.stopPropagation();
    var $target = $(a).closest(".city-picker-dropdown").data('target');
    $($target).find('.city-list').find(":checkbox").prop("checked", 0).eq(2).change();
    //.find('.city-list').eq(0).find(":checkbox").prop("checked", 0).eq(2).change();
    //$("#body").css("margin-top", 0);
    return false;
}



function setField(){
    var $value = $("#content-wrapper").find('h1.page-title').text();
    var $found = false;
    //check in company
    $("#edit-companies").find('option').each(function(){
        if($(this).text() == $value){
            $(this).attr('selected', 1);
            $found = true;
        }
    });


    //check in city picker
    if(!$found){
        $("#edit-destination-country").parent().find(".city-picker").find("input[type='checkbox']").each(function () {
            if($(this).data('name') == $value){
                $(this).click();//.attr('checked', true);
                $(this).closest(".form-item-destination-country").next(".city-picker-list").html($value + " <a href='#' onclick='return unCheckAll(this, event)' class='form-list-close'><span>x</span></a>");
                //$("#edit-destination-country").val($value);
            }
        });
    }

    ////check in country
    //if(!$found){
    //    $("#edit-destination-country").find('option').each(function(){
    //        if($(this).text() == $value){
    //            $(this).attr('selected', 1);
    //            $found = true;
    //        }
    //    });
    //}

    ////check in city
    //if(!$found){
    //    $("#edit-destination-city").find('option').each(function(){
    //        if($(this).text() == $value){
    //            $(this).attr('selected', 1);
    //            $found = true;
    //        }
    //    });
    //}

}


var $travelbase_destination_value = '';
function fill_destination_country($list, $cityPicker) {
    var $destination_country = $list;//$(".city-picker-list");
    $destination_country.text();
    $travelbase_destination_value = "";

    listCrawler($cityPicker.find("> div.city-list > div > ul[class!='ignore']"));

    $travelbase_destination_value = $travelbase_destination_value.substring(0, $travelbase_destination_value.length - 2);
    if($travelbase_destination_value == "") $travelbase_destination_value = "All";
    if($travelbase_destination_value.length > 120) $travelbase_destination_value = $travelbase_destination_value.substr(0, 120) + "...";

    if($travelbase_destination_value != "All")
        $destination_country.html( " <a href='#' onclick='return unCheckAll(this, event)' class='form-list-close'><span>&times;</span></a>" + $travelbase_destination_value);
    else
        $destination_country.html($travelbase_destination_value);
}

function listCrawler($uls){
    //go through each ul
    $uls.each(function () {
        //get total amount of checkboxes in ul
        var $amount = $(this).find('input[type=checkbox]').length;
        //get total amount of selected checkboxes in ul
        var $selected = $(this).find('input[type=checkbox]:checked').length;

        //if all checkboxes are checked add only this name
        if($amount == $selected){
            var $value = $(this).find("label").first().text();
            if($value != ""){
                $travelbase_destination_value += $(this).find("label").first().text() + ", ";
            }

        }
        //if not all selected in list
        else if($selected > 0 && $selected < $amount){
            //go through all child li
            $(this).find(" > li").each(function () {
                //if has more sub lists go through then
                if($(this).hasClass("parent-list")){
                    listCrawler($(this).find(" > ul"));
                }
                //add to destination
                else if ($(this).find("> input[type=checkbox]:checked").length > 0){
                    $travelbase_destination_value += $(this).find("label").first().text() + ", ";
                }
            });
        }
    });
}