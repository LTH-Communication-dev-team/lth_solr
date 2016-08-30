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

    if($('#query').val()) {
        //widget($('#query').val());
        searchResult($('#query').val(), 'searchLong', 0, 0);
    }

});


function searchResult(term, action, peopleOffset, documentsOffset)
{
    console.log(peopleOffset + ',' + documentsOffset);
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : action,
            term : term,
            peopleOffset: peopleOffset,
            documentsOffset: documentsOffset,
            /*pid : $('#pid').val(),
            pageid : $('body').attr('id'),
            scope : $('#lth_solr_scope').val(),
            sys_language_uid : $('#sys_language_uid').val(),
            categories : $('#lth_solr_categories').val(),
            custom_categories : $('#lth_solr_custom_categories').val(),
            categoriesThisPage : $('#categoriesThisPage').val(),
            introThisPage : $('#introThisPage').val(),
            addPeople : $('#addPeople').val(),*/
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: 'json',
        beforeSend: function () {
            if(action === 'searchMorePeople') {
                $('#morePeople').html('<img src="/fileadmin/templates/images/ajax-loader.gif" />');
            } else if(action === 'searchMoreDocuments') {
                $('#moreDocuments').html('<img src="/fileadmin/templates/images/ajax-loader.gif" />');
            } else {
                $('#solrsearchresult').html('<img src="/fileadmin/templates/images/ajax-loader.gif" />');
            }
        },
        success: function(d) {
            var i = 0;
            var maxClass = '';
            var more = '';
            var count = '';
            var facet = '';
            var content = '';

            if(peopleOffset == 0 && documentsOffset == 0) $('#solrsearchresult').html('');
            
            if(d.people) {
                if(peopleOffset > 0) {
                    $('#morePeople').before(d.people);
                    if(d.peopleNumFound > (parseInt(peopleOffset)+10)) {
                        $('#morePeople').html('<a href="#" onclick="searchResult(\'' + term + '\', \'searchMorePeople\', \'' + (parseInt(peopleOffset)+10) + '\',\'' + documentsOffset + '\'); return false;">Visa fler</a></li>');
                    } else {
                        $('#morePeople').html('');
                    }
                } else {
                    $('#solrsearchresult').append('<ul><li><h2>PEOPLE</h2>(' + d.peopleNumFound + ' hits)</li>' + d.people + '</ul>');
                }
            }
            
            if(d.documents) {
                if(documentsOffset > 0) {
                    $('#moreDocuments').before(d.documents);
                    if(d.documentsNumFound > (parseInt(documentsOffset)+10)) {
                        $('#moreDocuments').html('<a href="#" onclick="searchResult(\'' + term + '\', \'searchMoreDocuments\', \'' + peopleOffset + '\',\'' + (parseInt(documentsOffset)+10) + '\'); return false;">Visa fler</a></li>');
                    } else {
                        $('#moreDocuments').html('');
                    }
                } else {
                    $('#solrsearchresult').append('<ul><li><h2>DOCUMENTS</h2>(' + d.documentsNumFound + ' hits)</li>' + d.documents + '</ul>');
                }
            }

            if(d.facet) {
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
            }
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
}


function widget(query)
{
    //console.log(query);
    /*solr = {sid: 'sid-3b9f1f9ab29e40a5654b',q:query,p:1, url:'search'};         
    d = new Date();
    //function async_load(){
    var s = document.createElement('script');
    s.type = 'text/javascript';
    s.async = true;
    s.src = 'http://solr.search.lu.se:8899/loader.js?'+ d.getTime()).slice(0,5);
    var x = document.getElementsByTagName('script')[0];
    x.parentNode.insertBefore(s,x);*/
    solr = {sid: 'sid-07856cbc0c3c046c4f20',q:query,p:1, url:'search'};
    d = new Date();
    var s=document.createElement('script');
    s.type = 'text/javascript';
    s.async = true;
    s.src = 'http://solr.search.lu.se:8899/loader.js?'+ ('' + d.getTime()).slice(0,5);
    var x=document.getElementsByTagName('script')[0];
    x.parentNode.insertBefore(s,x);
}


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
                    buttons: [
                        {
                            extend: 'collection',
                            text: 'Export',
                            buttons: [
                                'excel',
                                'csv'
                            ]
                        }//,
                        //'colvis'
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
                        
                        if(aData[4]) {
                            for (i = 0; i < aData[4].length; i++) {
                                if(phone) {
                                    phone += ', ';
                                } else {
                                    phone += lth_solr_messages.phone + ': ';
                                }
                                phone += aData[4][i];
                            }
                        }
                        
                        if(aData[14]) {
                            for (i = 0; i < aData[14].length; i++) {
                                if(phone) {
                                    phone += ', ';
                                } else {
                                    phone += lth_solr_messages.phone + ': ';
                                }
                                phone += aData[14][i];
                            }
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
                        
                        var homePage = '';
                        if(aData[10]) {
                            homePage = lth_solr_messages.personal_homepage + ': <a href="' + aData[10] + '">' + aData[10] + '</a>';
                        } else if(aData[15]) {
                            homePage = '<a href="' + window.location.href + 'presentation_single_person_right?query='+aData[15]+'&action=detail&sid='+Math.random()+'">Läs mer om ' + display_name_t + '</a>';
                        }
                        template = template.replace('###homepage_t###', homePage);
                        
                        template = template.replace('###image_t###', aData[11]);
                        template = template.replace('###lth_solr_intro###', aData[12]);
                        
                        var roomNumber = aData[13];
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
            if(d.personData) {                                 
                var template = $('#solrTemplate').html();
                //console.log(d.data);
                var display_name_t = d.personData[0] + ' ' + d.personData[1];
                        
                template = template.replace(/###display_name_t###/g, display_name_t);
                $('article header h1').text(display_name_t).show();
                var title, title_t = '', title_en_t = '', oname, oname_t = '', oname_en_t = '', phone = '', roomNumber = '';
                //console.log(d.personData);

                if(d.personData[2]) {
                    for (i = 0; i < d.personData[2].length; i++) {
                        if(title_t) {
                            title_t += ', ';
                        }
                        if(d.personData[2][i]) title_t += d.personData[2][i];
                    }
                }
                if(d.personData[3]) {
                    for (i = 0; i < d.personData[3].length; i++) {
                        if(title_en_t) {
                            title_en_t += ', ';
                        }
                        title_en_t += d.personData[3][i];
                    }
                }
                if(lth_solr_lang == 'en' && title_en_t) {
                    title = title_en_t;
                } else if(title_t) {
                    title = title_t;
                } 

                template = template.replace('###title_t###', titleCase(title));

                if(d.personData[4]) {
                    for (i = 0; i < d.personData[4].length; i++) {
                        if(phone) {
                            phone += ', ';
                        } else {
                            phone += lth_solr_messages.phone + ': ';
                        }
                        phone += d.personData[4][i];
                    }
                    template = template.replace('###phone_t###', phone);
                }

                template = template.replace(/###email_t###/g, d.personData[6]);

                if(d.personData[7]) {
                    for (i = 0; i < d.personData[7].length; i++) {
                        if(oname_t) {
                            oname_t += ', ';
                        }
                        oname_t += d.personData[7][i];
                    }
                }

                if(d.personData[7]) {
                    for (i = 0; i < d.personData[8].length; i++) {
                        if(oname_en_t) {
                            oname_en_t += ', ';
                        }
                        oname_en_t += d.personData[8][i];
                    }
                }

                if(lth_solr_lang == 'en' && oname_en_t) {
                    oname = oname_en_t;
                } else if(oname_t) {
                    oname = oname_t;
                } 
                template = template.replace('###oname_t###', oname);

                template = template.replace('###primary_affiliation_t###', d.personData[9]);

                var homePage = d.personData[10];
                if(homePage) {
                    homePage = lth_solr_messages.personal_homepage + ': <a href="' + homePage + '">' + homePage + '</a>';
                } /*else {
                    homePage = '<a href="/testarea/staff-list/presentation_single_person_left?query='+d.personData[5]+'">Läs mer om ' + display_name_t + '</a>';
                }*/
                template = template.replace('###homepage_t###', homePage);
                template = template.replace('###image_t###', d.personData[11]);
                template = template.replace('###lth_solr_intro###', d.personData[12]);

                var roomNumber = d.personData[13];
                if(roomNumber) {
                    roomNumber = '(' + lth_solr_messages.room + ' ' + d.personData[13] + ')';
                }
                template = template.replace('###room_number_txt###', roomNumber);
                
                
                /*var publicationType = d.data[14];
                template = template.replace('###publicationType###', publicationType);
                
                var portalUrl = d.data[15];
                template = template.replace('###portalUrl###', portalUrl);

                
                template = template.replace('###publicationDate###', publicationDate);
                
                var person = d.data[17];
                template = template.replace('###person###', person);
                
                var abstract_en = d.data[18];
                template = template.replace('###abstract_en###', abstract_en);
                
                var abstract_sv = d.data[19];
                template = template.replace('###abstract_sv###', abstract_sv);*/
                
                
            }
            var publicationList = '';
            if(d.publicationData) {
                publicationList += '<h2>Publication list</h2><ul>';
                $(d.publicationData).each(function() {
                    publicationList += '<li><h3><a href="' + this[2] + '">' + this[0] + '</h3></a></li>';
                    publicationList += '<li>' + this[1] + '</li>';
                    publicationList += '<li>' + this[2] + '</li>';
                    publicationList += '<li>' + this[3] + '</li>';
                    if(this[6]) {
                        publicationList += '<li>' + this[6].substr(0,50) + '</li>';
                    } else if(this[5]) {
                        publicationList += '<li>' + this[5].substr(0,50) + '</li>';
                    }
                });
                publicationList += '</ul>';
            }
            
            if(publicationList) {
                template = template.replace('###publicationList###', publicationList);
            }
            
            $('#lthsolr_table').html(template);
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
            url : 'http://connector.search.lu.se:8181/solr/sr/130.235.208.15/sid-/' + query + '/customsites/1/undefined?' + d.getTime() + '-sid-d86c248d60b4072f018c',
            //url: 'http://connector.search.lu.se:8181/solr/ac/130.235.208.15/sid-/' + query + '/customsites',
}*/