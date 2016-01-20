var lthClassesToShow = Array('dt-buttons');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function() {
    var lth_solr_type = $('#lth_solr_type').val();
    if(lth_solr_type === 'list') {
        lthSolrList();
    } else if(lth_solr_type === 'detail') {
        lthSolrDetail();
    } else if(lth_solr_type === 'rest') {
        lthSolrRest();
    }

    if($('#fe_user').val()) {
        $('#lth_solr_facet_container').append('<div id="lth_solr_helper"><a href="/testarea/kommunikation-och-samverkan/t3reg">Edit image and short text</a></div>');
    } else if($('#lu_user').val()) {
        //console.log('??????????????????????');
        $('#lth_solr_facet_container').append('<div id="lth_solr_helper">Use the link above to the right to login and edit image and text.</div>');
    }
});


function lthSolrRest()
{
    $.ajax({
        type : "POST",
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'rest',
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: "xml",
        beforeSend: function () {
            $('#lthsolr_table tbody').html('<img src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(xml) {
            if(xml) {
                var display_name_t;
                $(xml).find('name').each(function() {
                    display_name_t += $(this).find('firstName').text();
                    display_name_t += ' ' + $(this).find('lastName').text();
                });
                
                var template = $('#solrTemplate').html();
                template = template.replace(/###display_name_t###/g, display_name_t);

                $('#lthsolr_table').html(template);
            }
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
}


function lthSolrList()
{
    $.fn.dataTableExt.oSort['full_name-asc'] = function(x,y) {
        var last_name_x = x.split(" ")[1];
        var last_name_y = y.split(" ")[1];
        return ((last_name_x < last_name_y) ? -1 : ((last_name_x > last_name_y) ?  1 : 0));
    };

    $.fn.dataTableExt.oSort['full_name-desc'] = function(x,y) {
        var last_name_x = x.split(" ")[1];
        var last_name_y = y.split(" ")[1];
        return ((last_name_x < last_name_y) ?  1 : ((last_name_x > last_name_y) ? -1 : 0));
    };
    
    $.ajax({
        type : "POST",
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'facetSearch',
            table_length : 25,
            pid : $('#pid').val(),
            pageid : $('body').attr('id'),
            scope : $('#lth_solr_scope').val(),
            sys_language_uid : $('#sys_language_uid').val(),
            custom_categories : $('#lth_solr_custom_categories').val(),
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: "json",
        beforeSend: function () {
            $('#lthsolr_table tbody').html('<img src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            if(d.data) {
                var i = 0;
                var maxClass = '';
                var more = '';
                var count = '';
                var facet = '';
                var content = '';

                $.each( d.facet, function( key, value ) {
                    $.each( value, function( key1, value1 ) {
                        if(i > 5) {
                            maxClass = ' class="maxlist-hidden"';
                            more = '<p class="maxlist-more"><a href="#">Visa alla</a></p>';
                        }
                        
                        facet = value1[0];
                        count = value1[1];
                        if(parseInt(value1[1]) > 0) {
                            content += '<li' + maxClass + '>' + facet.split('$').shift().capitalize() + ' [' + count + '] ';
                            content += '<input type="checkbox" class="lth_solr_facet" name="lth_solr_facet" value="' + key.split('$').shift() + '###' + facet.split('$').shift() + '"></li>';
                        }
                        i++;
                    });
                    $('#lth_solr_facet_container').append('<div class="item-list"><ul>' + content + '</ul>' + more + '</div>');
                    i=0;
                    maxClass='';
                    more='';
                    content = '';
                });

                $('.maxlist-more').click(function(){
                    $(this).parent().find('.maxlist-hidden').toggle();
                    $(this).text(function(i, text){
                        return text === "Visa alla" ? "Visa urval" : "Visa alla";
                    });
                    return false;
                });

                //return d.data;
                //var result = $('#lth_solr_template').tmpl(d.data);
                //$('#lth_solr_data_container').empty().append(result);
                        
                
                var table = $('#lthsolr_table').DataTable({
                    aoColumns : [{  "sType": "full_name" }],
                    data : d.data,
                    sPaginationType : "full_numbers",
                    aaSorting : [],
                    pageLength : 25,
                    //"bJQueryUI": true,
                    //"bDestroy": true,
                    dom : 'lBfrtip',
                    buttons : [
                        'copyHtml5',
                        'excelHtml5',
                        'csvHtml5',
                        'pdfHtml5'
                    ],
                    fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                        var template = $('#solrTemplate').html();
                        var detailPage = $('#lth_solr_detailpage').val();
                        var homePage = aData[8];
                        if((aData[8] === '' || !aData[8]) && detailPage) {
                            homePage = '?id=' + detailPage + '&solrid=' + aData[3];
                        }

                        template = template.replace(/###display_name_t###/g, aData[0]);
                        var title_t = '';
                        //console.log(aData);
                        $(aData[1]).each(function() {
                            //console.log($(this));
                            title_t += $(this).text() + '<br />';
                        });
                        template = template.replace('###title_t###', title_t);
                        template = template.replace('###phone_t###', aData[2]);
                        template = template.replace('###email_t###', aData[4]);
                        template = template.replace('###ou_t###', ' ' + aData[5]);
                        template = template.replace('###orgid_t###', aData[6]);
                        template = template.replace('###primary_affiliation_t###', aData[7]);
                        template = template.replace('###homepage_t###', homePage);
                        template = template.replace('###image_t###', aData[9]);
                        template = template.replace('###lth_solr_intro###', aData[10]);
                        template = template.replace('###lth_solr_txt###', aData[11]);
                        $(nRow).html(template);
                        /*if (aData[1].indexOf('Unread') >= 0)
                            $(nRow).css('font-weight', 'bold');
                        else
                            $(nRow).css('font-weight', 'normal');
                        */
                    }
                });
                
                createFacetClick(table);
            }
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
}


function lthSolrDetail()
{
    $.ajax({
        type : "POST",
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'detail',
            pid : $('#pid').val(),
            pageid : $('body').attr('id'),
            scope : $('#lth_solr_scope').val(),
            sys_language_uid : $('#sys_language_uid').val(),
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: "json",
        beforeSend: function () {
            $('#lthsolr_table tbody').html('<img src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            if(d.data) {                                 
                var template = $('#solrTemplate').html();
                //console.log(d.data);
                template = template.replace(/###display_name_t###/g, d.data[0]);
                
                template = template.replace('###title_t###', d.data[1]);
                template = template.replace('###phone_t###', d.data[2]);
                template = template.replace('###email_t###', d.data[4]);
                template = template.replace('###ou_t###', ' ' + d.data[5]);
                template = template.replace('###orgid_t###', d.data[6]);
                template = template.replace('###primary_affiliation_t###', d.data[7]);
                template = template.replace('###image_t###', d.data[9]);
                template = template.replace('###room_number_txt###', d.data[10]);
                template = template.replace('###maildelivery_txt###', d.data[11]);
                template = template.replace('###lth_solr_intro###', d.data[12]);
                template = template.replace('###lth_solr_txt###', d.data[13]);
                $('#lthsolr_table').html(template);
            }
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
}


String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}


function createFacetClick(table)
{
    $('.lth_solr_facet').click(function() {
        
        //console.log();
        var facet = [];
        $("#lth_solr_facet_container input[type=checkbox]").each(function() {
            if($(this).prop('checked')) {
                facet.push($(this).val());
            };
        });
        
        $.ajax({
        type : "POST",
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            pid : $('#pid').val(),
            pageid : $('body').attr('id'),
            scope : $('#lth_solr_scope').val(),
            sys_language_uid : $('#sys_language_uid').val(),
            action : 'facetSearch',
            custom_categories : $('#custom_categories').val(),
            facet : JSON.stringify(facet),
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: "json",
        beforeSend: function () {
            $('#lthsolr_table tbody').html('<img src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(data) {
            if(data) {
                table.clear().draw();
                table.rows.add(data.data); // Add new data
                table.columns.adjust().draw(false); // Redraw the DataTable
            }
        },
        complete: function(data) {
           
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
    });
}

/*function lthSolrGetCookie(cname)
{
    var name = cname + "=";
    console.log(document.cookie);
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) != -1) return c.substring(name.length,c.length);
    }
    return "";
}*/