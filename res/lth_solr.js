var lthClassesToShow = Array('dt-buttons');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function() {
    var lth_solr_type = $('#lth_solr_type').val();
    var lth_solr_lang = $('html').attr('lang');
    if(lth_solr_type === 'list') {
        lthSolrList(lth_solr_lang);
    } else if(lth_solr_type === 'detail') {
        lthSolrDetail(lth_solr_lang);
    }

    //if($('#fe_user').val()) {
        //$('#lth_solr_facet_container').append('<div id="lth_solr_helper"><a href="/testarea/kommunikation-och-samverkan/t3reg">Edit image and short text</a></div>');
    /*} else if($('#lu_user').val()) {
        $('#lth_solr_facet_container').append('<div id="lth_solr_helper">Use the link above to the right to login and edit image and text.</div>');
    }*/
});


function lthSolrList(lth_solr_lang)
{
    $.fn.dataTableExt.oSort['last_name-asc'] = function(x,y) {
        var last_name_x = x.split(" ")[1];
        var last_name_y = y.split(" ")[1];
        return ((last_name_x < last_name_y) ? -1 : ((last_name_x > last_name_y) ?  1 : 0));
    };

    $.fn.dataTableExt.oSort['last_name-desc'] = function(x,y) {
        var last_name_x = x.split(" ")[1];
        var last_name_y = y.split(" ")[1];
        return ((last_name_x < last_name_y) ?  1 : ((last_name_x > last_name_y) ? -1 : 0));
    };
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'facetSearch',
            table_length : 25,
            pid : $('#pid').val(),
            pageid : $('body').attr('id'),
            scope : $('#lth_solr_scope').val(),
            sys_language_uid : $('#sys_language_uid').val(),
            categories : $('#lth_solr_categories').val(),
            custom_categories : $('#lth_solr_custom_categories').val(),
            categoriesThisPage : $('#categoriesThisPage').val(),
            introThisPage : $('#introThisPage').val(),
            addPeople : $('#addPeople').val(),
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: 'json',
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
                            more = '<p class="maxlist-more"><a href="#">' + lth_solr_messages.show_all + '</a></p>';
                        }
                        
                        facet = value1[0];
                        count = value1[1];
                        if(parseInt(value1[1]) > 0) {
                            content += '<li' + maxClass + '>' + facet.split('$').shift().capitalize().replace(/_/g, ' ') + ' [' + count + '] ';
                            content += '<input type="checkbox" class="lth_solr_facet" name="lth_solr_facet" value="' + key.split('$').shift() + '###' + facet.split('$').shift() + '"></li>';
                        }
                        i++;
                    });
                    $('#lth_solr_facet_container').append('<div class="item-list"><ul><li><b>' + lth_solr_messages.staff_categories + '</b></li>' + content + '</ul>' + more + '</div>');
                    i=0;
                    maxClass='';
                    more='';
                    content = '';
                });

                $('.maxlist-more').click(function(){
                    $(this).parent().find('.maxlist-hidden').toggle();
                    $(this).text(function(i, text){
                        return text === lth_solr_messages.show_all ? lth_solr_messages.show_selection : lth_solr_messages.show_all;
                    });
                    return false;
                });

                //return d.data;
                //var result = $('#lth_solr_template').tmpl(d.data);
                //$('#lth_solr_data_container').empty().append(result);
                var exportArray;
                if(lth_solr_lang=='sv') {
                    exportArray = [0,1,2,4,6,13];
                } else {
                    exportArray = [0,1,3,4,6,13];
                }
                        
                var table = $('#lthsolr_table').DataTable({
                    language: {
                        url: 'typo3conf/ext/lth_solr/res/datatables_' + lth_solr_lang + '.json'
                    },
                    //aoColumns : [{  "sType": "full_name" }],
                    "columnDefs": [
                        { 'orderData':[1], 'targets': [0] },
                        {
                            "targets": [ 1 ],
                            "visible": false,
                            "searchable": false
                        },
                        {
                            "targets": [ 2 ],
                            "visible": false,
                            "searchable": false
                        },
                        {
                            "targets": [ 3 ],
                            "visible": false,
                            "searchable": false
                        },
                        {
                            "targets": [ 4 ],
                            "visible": false,
                            "searchable": false
                        },
                        {
                            "targets": [ 5 ],
                            "visible": false,
                            "searchable": false
                        },
                        {
                            "targets": [ 6 ],
                            "visible": false,
                            "searchable": false
                        },
                        {
                            "targets": [ 7 ],
                            "visible": false,
                            "searchable": false
                        },
                        {
                            "targets": [ 8 ],
                            "visible": false,
                            "searchable": false
                        },
                        {
                            "targets": [ 9 ],
                            "visible": false,
                            "searchable": false
                        },
                        {
                            "targets": [ 10 ],
                            "visible": false,
                            "searchable": false
                        },
                        {
                            "targets": [ 11 ],
                            "visible": false,
                            "searchable": false
                        },
                        {
                            "targets": [ 12 ],
                            "visible": false,
                            "searchable": false
                        },
                        {
                            "targets": [ 13 ],
                            "visible": false,
                            "searchable": false
                        } 
                    ],
                    data : d.data,
                    sPaginationType : "full_numbers",
                    aaSorting: [[1,'asc'], [0,'asc']],
                    pageLength : 25,
                    //"bJQueryUI": true,
                    //"bDestroy": true,
                    dom : 'lBfrtip',
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    buttons : [
                    {
                        extend: 'copyHtml5',
                        exportOptions: {
                            columns: exportArray
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        exportOptions: {
                            columns: exportArray
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        exportOptions: {
                            columns: exportArray
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        exportOptions: {
                            columns: exportArray
                        }
                    }
                    ],
                    initComplete: function () {
                        var info = '&nbsp;' + lth_solr_messages.of + '&nbsp;' + d.data.length;
                        $('.dataTables_length').append('<label>' + info + '</label>');
                    },
                    fnHeaderCallback: function( nHead, aData, iStart, iEnd, aiDisplay ) {
                        var info = '&nbsp;av&nbsp;' + aData.length + ' rader';
                        $('.dataTables_length').find('label:nth-child(2)').html('<label>' + info + '</label>');
                        //console.log("Displaying "+(aData.length)+" records");
                    },
                    fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                        var template = $('#solrTemplate').html();
                        //var detailPage = $('#lth_solr_detailpage').val();
                        var display_name_t = aData[0] + ' ' + aData[1];
                        
                        template = template.replace(/###display_name_t###/g, display_name_t);
                        var title, title_t = '', title_en_t = '', oname, oname_t = '', oname_en_t = '', phone = '', roomNumber = '';
                        //console.log(aData);

                        if(aData[2]) {
                            for (i = 0; i < aData[2].length; i++) {
                                if(title_t) {
                                    title_t += ', ';
                                }
                                if(aData[2][i]) title_t += aData[2][i];
                            }
                        }
                        if(aData[3]) {
                            for (i = 0; i < aData[3].length; i++) {
                                if(title_en_t) {
                                    title_en_t += ', ';
                                }
                                title_en_t += aData[3][i];
                            }
                        }
                        if(lth_solr_lang == 'en' && title_en_t) {
                            title = title_en_t;
                        } else if(title_t) {
                            title = title_t;
                        } 
                        
                        template = template.replace('###title_t###', titleCase(title));
                        
                        for (i = 0; i < aData[4].length; i++) {
                            if(phone) {
                                phone += ', ';
                            } else {
                                phone += lth_solr_messages.phone + ': ';
                            }
                            phone += aData[4][i];
                        }
                        template = template.replace('###phone_t###', phone);
                        
                        template = template.replace(/###email_t###/g, aData[6]);
                        
                        for (i = 0; i < aData[7].length; i++) {
                            if(oname_t) {
                                oname_t += ', ';
                            }
                            oname_t += aData[7][i];
                        }
                        for (i = 0; i < aData[8].length; i++) {
                            if(oname_en_t) {
                                oname_en_t += ', ';
                            }
                            oname_en_t += aData[8][i];
                        }
                        if(lth_solr_lang == 'en' && oname_en_t) {
                            oname = oname_en_t;
                        } else if(oname_t) {
                            oname = oname_t;
                        } 
                        template = template.replace('###oname_t###', oname);
                        
                        template = template.replace('###primary_affiliation_t###', aData[9]);
                        
                        var homePage = aData[10];
                        if(homePage) {
                            homePage = lth_solr_messages.personal_homepage + ': <a href="' + homePage + '">' + homePage + '</a>';
                        } /*else {
                            homePage = '<a href="/testarea/staff-list/presentation_single_person_left?solrid='+aData[5]+'">Läs mer om ' + display_name_t + '</a>';
                        }*/
                        template = template.replace('###homepage_t###', homePage);
                        
                        template = template.replace('###image_t###', aData[11]);
                        template = template.replace('###lth_solr_intro###', aData[12]);
                        
                        var roomNumber = aData[12];
                        if(roomNumber) {
                            roomNumber = '(' + lth_solr_messages.room + ' ' + aData[13] + ')';
                        }
                        template = template.replace('###room_number_s###', roomNumber);
                        $(nRow).html(template);
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


function titleCase(string) 
{
    if(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
}


function lthSolrDetail(lth_solr_lang)
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
                var display_name_t = d.data[0] + ' ' + d.data[1];
                        
                        template = template.replace(/###display_name_t###/g, display_name_t);
                        $('article header h1').text(display_name_t).show();
                        var title, title_t = '', title_en_t = '', oname, oname_t = '', oname_en_t = '', phone = '', roomNumber = '';
                        //console.log(d.data);

                        if(d.data[2]) {
                            for (i = 0; i < d.data[2].length; i++) {
                                if(title_t) {
                                    title_t += ', ';
                                }
                                if(d.data[2][i]) title_t += d.data[2][i];
                            }
                        }
                        if(d.data[3]) {
                            for (i = 0; i < d.data[3].length; i++) {
                                if(title_en_t) {
                                    title_en_t += ', ';
                                }
                                title_en_t += d.data[3][i];
                            }
                        }
                        if(lth_solr_lang == 'en' && title_en_t) {
                            title = title_en_t;
                        } else if(title_t) {
                            title = title_t;
                        } 
                        
                        template = template.replace('###title_t###', titleCase(title));
                        
                        if(d.data[4]) {
                            for (i = 0; i < d.data[4].length; i++) {
                                if(phone) {
                                    phone += ', ';
                                } else {
                                    phone += lth_solr_messages.phone + ': ';
                                }
                                phone += d.data[4][i];
                            }
                            template = template.replace('###phone_t###', phone);
                        }
                    
                        template = template.replace(/###email_t###/g, d.data[6]);
                        
                        if(d.data[7]) {
                            for (i = 0; i < d.data[7].length; i++) {
                                if(oname_t) {
                                    oname_t += ', ';
                                }
                                oname_t += d.data[7][i];
                            }
                        }
                        
                        if(d.data[7]) {
                            for (i = 0; i < d.data[8].length; i++) {
                                if(oname_en_t) {
                                    oname_en_t += ', ';
                                }
                                oname_en_t += d.data[8][i];
                            }
                        }
                        
                        if(lth_solr_lang == 'en' && oname_en_t) {
                            oname = oname_en_t;
                        } else if(oname_t) {
                            oname = oname_t;
                        } 
                        template = template.replace('###oname_t###', oname);
                        
                        template = template.replace('###primary_affiliation_t###', d.data[9]);
                        
                        var homePage = d.data[10];
                        if(homePage) {
                            homePage = lth_solr_messages.personal_homepage + ': <a href="' + homePage + '">' + homePage + '</a>';
                        } /*else {
                            homePage = '<a href="/testarea/staff-list/presentation_single_person_left?query='+d.data[5]+'">Läs mer om ' + display_name_t + '</a>';
                        }*/
                        template = template.replace('###homepage_t###', homePage);
                        template = template.replace('###image_t###', d.data[11]);
                        template = template.replace('###lth_solr_intro###', d.data[12]);
                        
                        var roomNumber = d.data[12];
                        if(roomNumber) {
                            roomNumber = '(' + lth_solr_messages.room + ' ' + d.data[13] + ')';
                        }
                        template = template.replace('###room_number_s###', roomNumber);
                                    if(d.lucris) {
                template = template.replace('###description###', d.lucris);
                $('#lthsolr_table').html(template);
            }
                        
                
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