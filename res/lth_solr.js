var lthClassesToShow = Array('dt-buttons');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function() {

    if($('#query').val()) {
        //widget($('#query').val());
        searchResult($('#query').val(), 'searchLong', 0, 0);
    }
    
    if($('#lth_solr_action').val() == 'listStaff') {
        listStaff();
    } else if($('#lth_solr_action').val() == 'showStaff') {
        showStaff();
    } else if($('#lth_solr_action').val() == 'listPublications') {
        listPublications();
    } else if($('#lth_solr_action').val() == 'listProjects') {
        listProjects();
    } else if($('#lth_solr_action').val() == 'showPublication') {
        showPublication();
    } else if($('#lth_solr_action').val() == 'showProject') {
        showProject();
    }

});


function listStaff()
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
    
    var syslang = $('#lth_solr_syslang').val();
    var scope = $('#lth_solr_scope').val();
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'listStaff',
            table_length : 25,
            pid : $('#pid').val(),
            pageid : $('body').attr('id'),
            scope : scope,
            syslang : syslang,
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
                if(syslang=='sv') {
                    exportArray = [0,1,2,4,6,13];
                } else {
                    exportArray = [0,1,3,4,6,13];
                }
                        
                var dt = $('#lthsolr_table').DataTable({
                    language: {
                        url: 'typo3conf/ext/lth_solr/res/datatables_' + syslang + '.json'
                    },
                    aoColumns : [{  "mData": 0},{ "mData": 1},{ "mData": 2}],
                    "columnDefs": [
                        { 
                            "orderData":[1], 
                            "targets": [ 0 ], 
                            "title": lth_solr_messages.namelabel 
                        },
                        {
                            "targets": [ 1 ],
                            "visible": false,
                            //"searchable": false,
                            "title": "last_name"
                        },
                        {
                            "targets": [ 2 ],
                            "visible": false,
                            //"searchable": false
                        },
                        {
                            "targets": [ 3 ],
                            "visible": false,
                            //"searchable": false
                        },
                        {
                            "targets": [ 4 ],
                            "visible": false,
                            //"searchable": false
                        },
                        {
                            "targets": [ 5 ],
                            "visible": false,
                            ////"searchable": false,
                            "title":"DT_RowId"
                        },
                        {
                            "targets": [ 6 ],
                            "visible": false,
                            //"searchable": false
                        },
                        {
                            "targets": [ 7 ],
                            "visible": false,
                            //"searchable": false
                        },
                        {
                            "targets": [ 8 ],
                            "visible": false,
                            //"searchable": false
                        },
                        {
                            "targets": [ 9 ],
                            "visible": false,
                            //"searchable": false
                        },
                        {
                            "targets": [ 10 ],
                            "visible": false,
                            //"searchable": false
                        },
                        {
                            "targets": [ 11 ],
                            "visible": false,
                            //"searchable": false
                        },
                        {
                            "targets": [ 12 ],
                            "visible": false,
                            //"searchable": false
                        },
                        {
                            "targets": [ 13 ],
                            "visible": false,
                            //"searchable": false
                        } 
                        ,
                        {
                            "targets": [ 14 ],
                            "visible": false,
                            //"searchable": false
                        } ,
                        {
                            "targets": [ 15 ],
                            "visible": false,
                            //"searchable": false
                        } ,
                        {
                            "targets": [ 16 ],
                            "visible": false,
                            //"searchable": false
                        } ,
                        {
                            "targets": [ 17 ],
                            "visible": false,
                            //"searchable": false
                        } 
                    ],
                    data : d.data,
                    sPaginationType : "full_numbers",
                    aaSorting: [],//[[1,'asc'], [0,'asc']],
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
                        var id = aData[15];
                        template = template.replace(/###id###/g, id);
                        
                        var display_name_t = aData[0] + ' ' + aData[1];
                        
                        template = template.replace(/###display_name_t###/g, display_name_t);
                        var title, title_t = '', title_en_t = '', oname = '', oname_t = '', oname_en_t = '', phone = '', roomNumber = '', homePage = '';
                        //console.log(aData);

                        /*if(aData[2]) {
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
                        
                        //mobile
                        if(aData[14]) {
                            for (i = 0; i < aData[14].length; i++) {
                                if(phone) {
                                    phone += ', ';
                                } else {
                                    phone += lth_solr_messages.phone + ': ';
                                }
                                phone += aData[14][i];
                            }
                        }*/
                        
                        
                        template = template.replace(/###email_t###/g, aData[6]);
                        
                        if(aData[17]) {
                            for (i = 0; i < aData[17].length; i++) {
                                //console.log(display_name_t + aData[17][i]);
                                if(inArray(scope, aData[17][i].split(','))) {
                                    if(aData[2]) title_t = aData[2][i];
                                    if(aData[3]) title_en_t = aData[3][i];
                                    if(aData[7]) oname_t = aData[7][i];
                                    if(aData[8]) oname_en_t = aData[8][i];
                                    if(aData[4]) phone = aData[4][i];
                                    if(aData[14]) phone += aData[14][i];
                                }
                            }
                        }
                        
                        if(syslang == 'en' && title_en_t) {
                            title = title_en_t;
                        } else if(title_t) {
                            title = title_t;
                        } 
                        
                        template = template.replace('###title_t###', titleCase(title));
                        template = template.replace('###phone_t###', phone);

                        /*if(aData[7]) {
                            for (i = 0; i < aData[7].length; i++) {
                                if(oname_t) {
                                    oname_t += ', ';
                                }
                                oname_t += aData[7][i];
                            }
                        }
                        
                        if(aData[8]) {
                            for (i = 0; i < aData[8].length; i++) {
                                if(oname_en_t) {
                                    oname_en_t += ', ';
                                }
                                oname_en_t += aData[8][i];
                            }
                        }
                        */
                        if(syslang == 'en' && oname_en_t) {
                            oname = oname_en_t;
                        } else if(oname_t) {
                            oname = oname_t;
                        } 
                        template = template.replace('###oname_t###', oname);
                        
                        template = template.replace('###primary_affiliation_t###', aData[9]);
                        
                        if(aData[10]) {
                            homePage = lth_solr_messages.personal_homepage + ': <a href="' + aData[10] + '">' + aData[10] + '</a>';
                        } else if(aData[15]) {
                            homePage = '<a href="' + window.location.href + 'presentation_single_person_right?query='+aData[15]+'&action=detail&sid='+Math.random()+'">Läs mer om ' + display_name_t + '</a>';
                        }
                        template = template.replace('###homepage_t###', homePage);
                        
                        template = template.replace('###image_t###', aData[11]);
                        template = template.replace('###lth_solr_intro###', aData[12]);
                        
                        roomNumber = aData[13];
                        if(roomNumber) {
                            roomNumber = '(' + lth_solr_messages.room + ' ' + aData[13] + ')';
                        } else {
                            roomNumber = '';
                        }
                        template = template.replace('###room_number_s###', roomNumber);
                        $(nRow).html(template);
                    }
                });
                
                $('#lthsolr_table tbody').on( 'click', 'tr', function () {
                    var lth_solr_detailpage = $('#lth_solr_detailpage').val();
                    if ( $(this).hasClass('selected') ) {
                        $(this).removeClass('selected');
                    } else if(lth_solr_detailpage) {
                        dt.$('tr.selected').removeClass('selected');
                        $(this).addClass('selected');
                        var id = $(this).find('div').attr('id');
                        //console.log(id);
                        window.location.href = lth_solr_detailpage + '?uuid=' + id;
                    }
                });
                
                createFacetClick(dt);
            }
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
}


function inArray(needle, haystack)
{
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}


function showMore()
{
    var readMoreText = $('.readmore').text();
    var scrollHeight = '';
    if(readMoreText === 'More') {
        scrollHeight = $('.more-content')[0].scrollHeight;
        $('.readmore').text('Close');
    } else {
        scrollHeight = '80';
        $('.readmore').text('More');
    }
    $('.more-content').css('height',scrollHeight+'px');
}


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


function format ( d ) {
    return ''+
        'publicationDateYear: ' + d.publicationDateYear+'<br>'+
        'abstract: ' + d.abstract_en;
}


function listPublications()
{
    var dt = $('#lthsolr_table').DataTable({
        //"processing": true,
        //"serverSide": true,
        "ajax": "index.php?eID=lth_solr&action=listPublications&table_length=10&scope=" + $('#lth_solr_scope').val() + "&sys_language_uid=" + $('#sys_language_uid').val() + "&sid=" +  Math.random(),
        "columns": [
            { "data": "title" },
            { "data": "authorName" },
            { "data": "publicationType_en" },
            { "data": "publicationDateYear" }
        ]
    });
    $('#lthsolr_table').on( 'click', 'tr', function () {
        var lth_solr_detailpage = $('#lth_solr_detailpage').val();
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
        } else if(lth_solr_detailpage) {
            dt.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var id = dt.row( this ).id();
            window.location.href = lth_solr_detailpage + '?uuid=' + id;
        }
    });
}


function showPublication()
{
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'showPublication',
            scope : $('#lth_solr_uuid').val(),
            syslang : $('#lth_solr_syslang').val(),
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: 'json',
        beforeSend: function () {
            $('#lth_solr_container').html('<img src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            if(d.data) {
                //console.log(d.data);
                $('#page_title h1').text(d.title);
                $('#lth_solr_container').html(d.data);
            }
        }
    });
}


function listProjects()
{
    var dt = $('#lthsolr_table').DataTable({
        //"processing": true,
        //"serverSide": true,
        "ajax": "index.php?eID=lth_solr&action=listProjects&table_length=10&scope=" + $('#lth_solr_scope').val() + "&sys_language_uid=" + $('#sys_language_uid').val() + "&sid=" +  Math.random(),
        "columns": [
            { "data": "title" },
            { "data": "participants" }
        ]
    });
    $('#lthsolr_table').on( 'click', 'tr', function () {
        var lth_solr_detailpage = $('#lth_solr_detailpage').val();
        if ( $(this).hasClass('selected') ) {
            $(this).removeClass('selected');
        } else if(lth_solr_detailpage) {
            dt.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var id = dt.row( this ).id();
            window.location.href = lth_solr_detailpage + '?no_cache=1&uuid=' + id;
        }
    });
}


function showProject()
{
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'showProject',
            scope : $('#lth_solr_uuid').val(),
            sys_language_uid : $('#sys_language_uid').val(),
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: 'json',
        beforeSend: function () {
            $('#lth_solr_container').html('<img src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            if(d.data) {
                //console.log(d.data);
                $('#page_title h1').text(d.title);
                $('#lth_solr_container').html(d.data);
            }
        }
    });
}


function titleCase(string) 
{
    if(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
}


function showStaff()
{
    var syslang = $('#sys_language_uid').val();
    
    $.ajax({
        type : "POST",
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'showStaff',
            pid : $('#pid').val(),
            pageid : $('body').attr('id'),
            scope : $('#lth_solr_uuid').val(),
            sys_language_uid : syslang,
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: "json",
        beforeSend: function () {
            /*$('#lthsolr_person_table').html('<img src="/fileadmin/templates/images/ajax-loader.gif" />');
            $('#lthsolr_publication_table').html('<img src="/fileadmin/templates/images/ajax-loader.gif" />');
            $('#lthsolr_project_table').html('<img src="/fileadmin/templates/images/ajax-loader.gif" />');*/
        },
        success: function(d) {
            if(d.personData) {                                 
                var template = $('#lthsolr_person_table').html();
                
                d.personData = d.personData[0];
                
                var display_name = d.personData[0] + ' ' + d.personData[1];
                        
                template = template.replace(/###display_name###/g, display_name);
                $('article header h1').text(display_name).show();
                var title = '', title_en = '', oname = '', oname_en = '', phone = '', roomNumber = '', homepage = '', image = '', primary_affiliation = '', lth_solr_intro = '';
                //console.log(d.personData);

                if(d.personData[2]) {
                    for (i = 0; i < d.personData[2].length; i++) {
                        if(title) {
                            title += ', ';
                        }
                        if(d.personData[2][i]) title += d.personData[2][i];
                    }
                }

                if(d.personData[3]) {
                    for (i = 0; i < d.personData[3].length; i++) {
                        if(title_en) {
                            title_en += ', ';
                        }
                        title_en += d.personData[3][i];
                    }
                }
                if(syslang == 'en' && title_en) {
                    title = title_en;
                }

                template = template.replace('###title###', titleCase(title));

                if(d.personData[4]) {
                    for (i = 0; i < d.personData[4].length; i++) {
                        if(phone) {
                            phone += ', ';
                        } else {
                            phone += lth_solr_messages.phone + ': ';
                        }
                        phone += d.personData[4][i];
                    }
                    template = template.replace('###phone###', phone);
                }

                template = template.replace(/###email###/g, d.personData[6]);

                if(d.personData[7]) {
                    for (i = 0; i < d.personData[7].length; i++) {
                        if(oname) {
                            oname += ', ';
                        }
                        oname += d.personData[7][i];
                    }
                }

                if(d.personData[7]) {
                    for (i = 0; i < d.personData[8].length; i++) {
                        if(oname_en) {
                            oname_en += ', ';
                        }
                        oname_en += d.personData[8][i];
                    }
                }

                if(syslang == 'en' && oname_en) {
                    oname = oname_en;
                }
                template = template.replace('###oname###', oname);

                template = template.replace('###primary_affiliation###', d.personData[9]);

                var homePage = d.personData[10];
                if(homePage) {
                    homePage = '<a href="' + homePage + '">' + homePage + '</a>';
                } /*else {
                    homePage = '<a href="/testarea/staff-list/presentation_single_person_left?query='+d.personData[5]+'">Läs mer om ' + display_name_t + '</a>';
                }*/
                template = template.replace('###homepage###', homePage);
                
                if(d.personData[11]) {
                    image = '<img src="' + d.personData[11] + '" style="height:200px;width:160px;" />';
                }
                template = template.replace('###image###', image);
                
                template = template.replace('###lth_solr_intro###', d.personData[12]);

                var roomNumber = d.personData[13];
                if(roomNumber) {
                    roomNumber = '(' + lth_solr_messages.room + ' ' + d.personData[13] + ')';
                }
                template = template.replace('###room_number###', roomNumber);
                
                $('#lthsolr_person_table tbody').html(template);
            }

            if(d.publicationData) {
                var dpu = $('#lthsolr_publication_table').DataTable({
                    //"processing": true,
                    //"serverSide": true,
                    data : d.publicationData,
                    "columns": [
                        { "data": "title" },
                        { "data": "authorName" },
                        { "data": "publicationType_en" },
                        { "data": "publicationDateYear" }
                    ]
                });
                $('#lthsolr_publication_table').on( 'click', 'tr', function () {
                    if ( $(this).hasClass('selected') ) {
                        $(this).removeClass('selected');
                    } else {
                        dpu.$('tr.selected').removeClass('selected');
                        $(this).addClass('selected');
                        var id = dpu.row( this ).id();
                        window.location.href = '/testarea/solr/publications/detail?uuid=' + id;
                    }
                });
            }
            
            if(d.projectData) {
                var dpa = $('#lthsolr_project_table').DataTable({
                    //"processing": true,
                    //"serverSide": true,
                    data : d.projectData,
                    "columns": [
                        { "data": "title" },
                        { "data": "participants" }
                    ]
                });
                $('#lthsolr_project_table').on( 'click', 'tr', function () {
                    if ( $(this).hasClass('selected') ) {
                        $(this).removeClass('selected');
                    } else {
                        dpa.$('tr.selected').removeClass('selected');
                        $(this).addClass('selected');
                        var id = dpa.row( this ).id();
                        window.location.href = '/testarea/solr/projects/detail?uuid=' + id;
                    }
                });
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
            action : 'listStaff',
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