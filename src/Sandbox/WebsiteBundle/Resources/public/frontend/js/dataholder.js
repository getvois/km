(function ( $ ) {
    // значение по умолчанию
    var defaults = {
        unique: true, //add only unique data values
        afterRemove: function(){} //after remove call back//todo does not work on instances
    };

    var options = [];

    // наши публичные методы
    var methods = {
        // инициализация плагина
        init:function(params) {
            // актуальные настройки, будут индивидуальными при каждом запуске
            options = $.extend({}, defaults, params);
            $(this).data('data', []);

            $(this).addClass('placeholder');
            $(this).addClass('placeholder-collapse');
            $(this).hide();
            $(this).prev().hide();
            var holder = $(this);
            $(this).click(function (e) {
                if(!$(e.target).hasClass('placeholder-item-remove')){
                    holder.prev().click();
                }
            });

            $(this).find('.placeholder-item').click(function (e) {
                e.stopPropagation();
            });
            //toggle select2

            $(this).prev().click(function (e) {
                e.stopPropagation();
                e.preventDefault();
                console.log(holder.hasClass('placeholder-collapse'));
                //if(!holder.hasClass('placeholder-collapse')){
                if($(holder.data('target')).is(":visible")){
                    $(holder.data('target')).prev().show();//.slideDown();
                    $(holder.data('target')).prev().prev().show();//.slideDown();
                    $(holder.data('target')).prev().find('input').focus().css('padding-left', '5px');
                }else{
                    $(holder.data('target')).prev().hide();//.slideUp();
                    $(holder.data('target')).prev().prev().hide();//.slideUp();
                    $(holder.data('target')).prev().find('input').css('padding-left', '42px');
                    //$($holder.data('target')).select2('open');//removed 31.03.16
                    $(holder.data('target')).show().focus();//added 31.06.16

                }
                holder.toggleClass('placeholder-collapse');


            });

            $(this).on('click', '.placeholder-item-remove', function (e) {
                e.preventDefault();
                var $itemToRemove = $(this).closest(".placeholder-item");
                var $holder = $itemToRemove.parent();
                $itemToRemove.remove();

                var $data = $holder.data('data');

                var $newData = [];
                $holder.find(".placeholder-item").each(function () {
                    $newData.push($(this).data('value').toString());
                });

                $holder.data('data', $newData);

                if($newData.length == 0) {
                    $holder.hide();//.slideUp();
                    $holder.prev().hide();
                    $($holder.data('target')).prev().show();//.slideDown();
                    $($holder.data('target')).prev().prev().show();//.slideDown();
                    $($holder.data('target')).prev().find('input').css('padding-left', '42px');
                    //$($holder.data('target')).select2('open'); //removed 31.03.16
                    $(holder.data('target')).show();//added 31.03.16
                }

                options.afterRemove.call($holder);//call back
            });

            return this;
        },
        // изменение цвета
        add:function(data) {

            var $data = $(this).data('data');

            if(options.unique){

                if(typeof data === 'object'){
                    //check if does not exist in data
                    if ($data.indexOf(data.id.toString()) == -1) {
                        $data.push(data.id.toString());
                        addElement(this, data);
                    }
                }else{
                    //check if does not exist in data
                    if ($data.indexOf(data) == -1) {
                        $data.push(data.toString());
                        addElement(this, data);
                    }
                }
            }else{
                $data.push(data);
                addElement(this, data);
            }
            $(this).data('data', $data);

            if($data.length > 0) {
                $(this).addClass('placeholder-collapse');
                $(this).show();//.slideDown();
                $(this).prev().show();//.slideDown();
                $($(this).data('target')).prev().hide();//.slideUp();
                $($(this).data('target')).prev().prev().hide();
            }

            return this;
        },
        afterRemove: function(callback){
            options.afterRemove = callback;
        },
        data: function(){
            return $(this).data('data');
        },
        setData: function (data) {
            if(data.length == 0){
                //$(this).hide();//.slideUp();
                //$(this).prev().hide();
                //$($(this).data('target')).prev().show();//.slideDown();
                //$($(this).data('target')).prev().prev().show();//.slideDown();
                //$($(this).data('target')).prev().find('input').css('padding-left', '42px');
            }else{
                for(var i = 0 ; i < data.length; i++){
                    $(this).add(data[i]);
                }
            }
        }
    };

    //todo make it public or in options
    function addElement(holder, data){
        if(typeof data === 'object'){
            holder.prepend("<span data-value='"+data.id+"' class=\'placeholder-item\'><a class=\"placeholder-item-remove\" tabindex=\"-1\" href=\"#\"></a>" + data.text + "</span>");

        }else{
            holder.prepend("<span data-value='"+data+"' class=\'placeholder-item\'><a class=\"placeholder-item-remove\" tabindex=\"-1\" href=\"#\"></a>" + data + "</span>");
        }
    }

    $.fn.dataHolder = function(method){
        // немного магии
        if ( methods[method] ) {
            // если запрашиваемый метод существует, мы его вызываем
            // все параметры, кроме имени метода прийдут в метод
            // this так же перекочует в метод
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            // если первым параметром идет объект, либо совсем пусто
            // выполняем метод init
            return methods.init.apply( this, arguments );
        } else {
            // если ничего не получилось
            $.error( 'Method "' +  method + '" not found in jQuery.dataholder' );
        }
    }
}( jQuery ));