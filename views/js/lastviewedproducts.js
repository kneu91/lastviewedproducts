/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function() {
        
    console.log(lastviewedproducts_ajax_url);

    var id = $('#product_page_product_id').val();

    if(id){
        $.get(lastviewedproducts_ajax_url, { id_products: id }, function (jsonData) {
        if (jsonData) {
            console.log(jsonData);
        }
//        $.ajax({
//        type: 'get',
//        url: lastviewedproducts_ajax_url,
//        data: {
//            id : id
//        },
//        dataType: 'json',            
//        success: function(responseArray) {
//            console.log(responseArray);
//            alert(responseArray);
//
//        }});  
    });  
    }

});