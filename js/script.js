        /* SELECT2 */
        // #city-input (main search form in header)
        // $('#city-input').click(function(){
        //     alert("aaa");
        // });
        $('#city-input').select2({
            ajax: {
                url: 'searchWhere.php',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    $("#city-input").html("");
                    return {
                        query: params.term, // search term
                        page: params.page
                    };
                    
                }
            },
            escapeMarkup: function(markup) {
                return markup;
            }, // let our custom formatter work
            minimumInputLength: 1,
            dropdownAutoWidth: true,
            placeholder: "Select a city",
            language: "en"
        });

        // $('#query-input').change(function(){
        //     console.log($(this).val()); 
        // });
        // #city-change (in modal triggered by navbar city change)
        // $('#city-change').select2({
        //     ajax: {
        //         url: 'searchWhere.php',
        //         dataType: 'json',
        //         delay: 250,
        //         data: function(params) {
        //             return {
        //                 query: params.term, // search term
        //                 page: params.page
        //             };
                    
        //         }
        //     },
        //     escapeMarkup: function(markup) {
        //         return markup;
        //     }, // let our custom formatter work
        //     minimumInputLength: 1,
        //     dropdownAutoWidth: true,
        //     placeholder: "Select a city",
        //     language: "en"
        // });
        // $(document.body).on('change', '#city-change', function() {
        //     alert(this.value);
        //     delete_cookie('city_name');
        //     createCookie('city_id', this.value, 90);
        //     location.reload(true);
        // });

        // $(document.body).on('click', '#clear-city', function(e) {
        //     e.preventDefault();
        //     delete_cookie('city_id');
        //     delete_cookie('city_name');
        //     location.reload(true);
        // });

        /* CUSTOM FUNCTIONS */
        // function createCookie(name, value, days) {
        //     var expires;
        //     var cookie_path;
        //     var path = "/directoryapp/directoryapp_108";
        //     alert("aaaa");
        //     if (days) {
        //         var date = new Date();
        //         date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        //         expires = "; expires=" + date.toUTCString();
        //     } else {
        //         expires = "";
        //     }

        //     if (path != '') {
        //         cookie_path = "; path=" + path;
        //     } else {
        //         cookie_path = "";
        //     }

        //     document.cookie = name + "=" + value + expires + cookie_path;
        // }

        // function delete_cookie(name) {
        //     alert(name);
        //     createCookie(name, "", -100);
        // }
    
        $(document).ready(function(){
            // $("#loading-image").hide();
            // $('#loading-image')
            //     .hide()
            //     .ajaxStart(function() {
            //         alert("start");
            //         $(this).show();
            //     })
            //     .ajaxStop(function() {
            //         alert("hide");
            //         $(this).hide();
            //     });
        });
        $("#btn_search").click(function(){

            query = $("#query-input").val();
            city = $("#city-input").text();
            
            if(query != "" && city != "")
                {
                    $('.loading').modal('toggle');
                    
                    $.ajax({
                    type: 'POST',
                    data: { 
                            query: query,
                            city :city
                        },
                    url: 'search.php',
                    dataType: 'html',
                    // async: false,
                    success: function(result){
                        // call the function that handles the response/results
                        // console.log(result);
                        $(".wrapper").html(result);
                        $('.loading').modal('toggle');
                        
                    },
                    error: function(){
                        
                        //window.alert("Wrong query : " + query);
                        $('.loading').modal('toggle');
                        $("#btn_search").click();
                    }
                  });
                }else{
                    alert("Please enter keywords and city to search!");

                }
        });
        