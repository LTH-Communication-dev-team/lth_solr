var lthClassesToShow = Array('dt-buttons');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function() {

    if($('#query').val()) {
        //widget($('#query').val());
        searchLong($('#query').val(), 'searchLong', 0, 0);
    }
    
    if($('#lth_solr_action').val() == 'listStaff') {
        listStaff(0);
    } else if($('#lth_solr_detail_action').val() == 'showStaff') {
        showStaff();
    } else if($('#lth_solr_action').val() == 'listPublications') {
        listPublications(0);
    } else if($('#lth_solr_action').val() == 'listProjects') {
        listProjects(0);
    } else if($('#lth_solr_action').val() == 'showPublication') {
        showPublication();
    } else if($('#lth_solr_action').val() == 'showProject') {
        showProject();
    }

    $('.lthsolr_filter').keyup(function() {
        var noQuery;
        if($(this).val().trim() === '') {
            noQuery = true;
        } else {
            noQuery = false;
        }
        listStaff(0, getFacets(), $(this).val().trim(), noQuery);
        /*if($(this).val().trim().length > 0) {
            //listStaff($(this).val().trim(), 0);
            //lthsolr_row
            $(".lthsolr_row").hide();
            $(".lthsolr_row:contains('" + $(this).val().trim() + "')" ).show();
        } else {
            $(".lthsolr_row").show();
        }*/
    });
});


function listStaff(tableStart, facet, query, noQuery, more)
{
    var syslang = $('#lth_solr_syslang').val();
    var scope = $('#lth_solr_scope').val();
    var tableLength = $('#lth_solr_no_items').val();

    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'listStaff',
            table_start: tableStart,
            table_length : tableLength,
            pid : $('#pid').val(),
            pageid : $('body').attr('id'),
            scope : scope,
            syslang : syslang,
            query: query,
            categories : $('#lth_solr_categories').val(),
            custom_categories : $('#lth_solr_custom_categories').val(),
            categoriesThisPage : $('#categoriesThisPage').val(),
            introThisPage : $('#introThisPage').val(),
            addPeople : $('#addPeople').val(),
            facet: facet,
            sid : Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            if((facet || query || noQuery) && (!more)) {
                $('#lthsolr_staff_container div').remove().append('<img id="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
            }
            //$('#lthsolr_all').remove();
            $('#lthsolr_more').replaceWith('<img id="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            if(d.data) {
                var i = 0;
                var maxClass = '';
                var more = '';
                var count = '';
                var facet = '';
                var content = '';

                if(d.facet) {
                    if($('.item-list').length == 0) {
                        $.each( d.facet, function( key, value ) {
                            $.each( value, function( key1, value1 ) {
                                if(i > 5) {
                                    maxClass = ' class="maxlist-hidden"';
                                    more = '<p class="maxlist-more"><a href="#">' + lth_solr_messages.show_all + '</a></p>';
                                }

                                facet = value1[0];
                                count = value1[1];
                                if(parseInt(value1[1]) > 0) {
                                    content += '<li' + maxClass + '>';
                                    content += facet.split('$').shift().capitalize().replace(/_/g, ' ') + ' [' + count + '] ';
                                    content += '<input type="checkbox" class="lth_solr_facet" name="lth_solr_facet" value="' + key.split('$').shift() + '###' + facet.split('$').shift() + '"></li>';
                                }
                                i++;
                            });
                            $('.lth_solr_facet_container').append('<div class="item-list"><ul><li>' + content + '</ul>' + more + '</div>');
                            i=0;
                            maxClass='';
                            more='';
                            content = '';
                        });
                        createFacetClick();
                    }
                    
                }
            
                $.each( d.data, function( key, aData ) {
                    var template = $('#solrTemplate').html();

                    var id = aData[15];
                    template = template.replace('###id###', id);

                    var display_name_t = aData[0] + ' ' + aData[1];

                    template = template.replace(/###display_name_t###/g, display_name_t);
                    var title, title_t = '', title_en_t = '', oname = '', oname_t = '', oname_en_t = '', phone = '', roomNumber = '', homePage = '';

                    template = template.replace(/###email_t###/g, aData[6]);

                    //if(aData[17]) {
                        //for (i = 0; i < aData[17].length; i++) {
                            //if(inArray(scope, aData[17][i].split(','))) {
                                if(aData[2]) title_t = aData[2][0];
                                if(aData[3]) title_en_t = aData[3][0];
                                if(aData[7]) oname_t = aData[7][0];
                                if(aData[8]) oname_en_t = aData[8][0];
                                if(aData[4]) {
                                    phone = aData[4][0];
                                    
                                }
                                if(phone) phone = phone.replace('+4646222', '+46 46 222 ').replace(/(.{2}$)/, ' $1');
                                if(aData[14]) {
                                    if(phone) phone += ', ';
                                    phone += aData[14][0];
                                }

                            //}
                        //}
                    //}
                        
                    if(syslang == 'en' && title_en_t) {
                        title = title_en_t;
                    } else if(title_t) {
                        title = title_t;
                    } 

                    template = template.replace('###title_t###', titleCase(title));
                    template = template.replace('###phone_t###', phone);

                    if(syslang == 'en' && oname_en_t) {
                        oname = oname_en_t;
                    } else if(oname_t) {
                        oname = oname_t;
                    } 
                    template = template.replace('###oname_t###', oname);

                    template = template.replace('###primary_affiliation_t###', aData[9]);

                    if(aData[10]) {
                        homePage = lth_solr_messages.personal_homepage + ': <a data-homepage="' + aData[10] + '" href="' + aData[10] + '">' + aData[10] + '</a>';
                    } else if(aData[15]) {
                        homePage = '<a href="' + window.location.href + 'presentation_single_person_right?query='+aData[15]+'&action=detail&sid='+Math.random()+'">Läs mer om ' + display_name_t + '</a>';
                    }
                    template = template.replace('###homepage_t###', '<p>' + homePage + '</p>');

                    var image = '';
                    if(aData[11]) image = '<div class="align_left" style="width:80px;"><img style="max-height: 100%; max-width: 100%" src="' + aData[11] + '" /></div>';
                    template = template.replace('###image_t###', image);
                    
                    template = template.replace('###lth_solr_intro###', aData[12].replace('\n','<br />'));

                    roomNumber = aData[13];
                    if(roomNumber) {
                        roomNumber = '(' + lth_solr_messages.room + ' ' + aData[13] + ')';
                    } else {
                        roomNumber = '';
                    }
                    template = template.replace('###room_number_s###', roomNumber);
                    $('#lthsolr_staff_container').append(template);
                });
                $('#lthsolr_loader').remove();
                
                $('#lthsolr_staff_header').html('1-' + maxLength(parseInt(tableStart) + parseInt(tableLength),parseInt(d.numFound)) + ' of ' + d.numFound);
                if((parseInt(tableStart) + parseInt(tableLength)) < d.numFound) {
                    $('#lthsolr_staff_container').append('<div style="margin-top:20px;" id="lthsolr_more"><a href="javascript:" onclick="listStaff(' + (parseInt(tableStart) + parseInt(tableLength)) + ',getFacets(),$(\'.lthsolr_filter\').val().trim(),false,true);">NEXT ' + tableLength + ' of ' + d.numFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.numFound + '); listStaff(' + (parseInt(tableStart) + parseInt(tableLength)) + ',getFacets(),$(\'.lthsolr_filter\').val().trim(),false,true);">Show all ' + d.numFound + '</a></div>');
                }
            }
            
            $('.lthsolr_row').on( 'click', function () {
                var lth_solr_detailpage = $('#lth_solr_staffdetailpage').val();
                if(lth_solr_detailpage) {
                    var id = $(this).attr('id');
                    //console.log(id);
                    if($(this).find('[data-homepage]').attr('href')) {
                        window.location.href = $(this).find('[data-homepage]').attr('href');
                    } else {
                        window.location.href = lth_solr_detailpage + '?no_cache=1&uuid=' + id;
                    }
                }
            });
            
        }
    });
}


function maxLength(tableLength, numFound)
{
    if(tableLength > numFound) {
        return numFound;
    } else {
        return tableLength;
    }
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


function searchLong(term, action, peopleOffset, documentsOffset)
{
    //console.log(peopleOffset + ',' + documentsOffset);
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
            
            if(d.facet) {
                $.each( d.facet, function( key, value ) {
                    //console.log(value);
                    //$.each( value, function( key1, value1 ) {
                        if(i > 5) {
                            maxClass = ' class="maxlist-hidden"';
                            more = '<p class="maxlist-more"><a href="#">' + lth_solr_messages.show_all + '</a></p>';
                        }

                       // if(parseInt(value[1]) > 0) {
                            content += '<li' + maxClass + '>' + value;
                            content += '<input type="checkbox" class="lth_solr_facet" name="lth_solr_facet" value="' + value + '"></li>';
                       // }
                        i++;
                    //});

                });
                $('.lth_solr_facet_container').append('<div class="item-list"><ul><li><b>' + lth_solr_messages.staff_categories + '</b></li>' + content + '</ul>' + more + '</div>');
                    i=0;
            }
            
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
                    //console.log(decodeURI(d.documents));
                    $('#solrsearchresult').append('<ul><li><h2>DOCUMENTS</h2>(' + d.documentsNumFound + ' hits)</li>' + d.documents + '</ul>');
                }
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


function listPublications(tableStart, facet, noFacet)
{
    var syslang = $('#lth_solr_syslang').val();
    var scope = $('#lth_solr_scope').val();
    var tableLength = $('#lth_solr_no_items').val();
    var lth_solr_detailpage = $('#lth_solr_publicationdetailpage').val();
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'listPublications',
            table_start: tableStart,
            table_length : tableLength,
            pid : $('#pid').val(),
            pageid : $('body').attr('id'),
            scope : scope,
            syslang : syslang,
            addPeople : $('#addPeople').val(),
            facet: facet,
            sid : Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            if(facet || noFacet) {
                $('#lthsolr_publications_container').append('<img id="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
            }
            //$('#lthsolr_all').remove();
            $('#lthsolr_more').replaceWith('<img id="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            if(d.data) {
                
                $.each( d.data, function( key, aData ) {
                    var template = $('#solrPublicationTemplate').html();

                    template = template.replace('###id###', aData[0]);
                    template = template.replace('###title###', aData[1]);
                    template = template.replace(/###authorName###/g, aData[2]);
                    template = template.replace(/###publicationType###/g, aData[3]);
                    template = template.replace(/###publicationDateYear###/g, aData[4]);
                    
                    $('#lthsolr_publications_container').append(template);
                });
                
                $('#lthsolr_loader').remove();

                $('#lthsolr_publications_header').html('1-' + maxLength(parseInt(tableStart) + parseInt(tableLength),parseInt(d.numFound)) + ' of ' + d.numFound);
                if((parseInt(tableStart) + parseInt(tableLength)) < d.numFound) {
                    $('#lthsolr_publications_container').append('<div style="margin-top:20px;" id="lthsolr_more"><a href="javascript:" onclick="listPublications(' + (parseInt(tableStart) + parseInt(tableLength)) + ');">NEXT ' + tableLength + ' of ' + d.numFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.numFound + '); listPublications(' + (parseInt(tableStart) + parseInt(tableLength)) + ');">Show all ' + d.numFound + '</a></div>');
                }
            }
            
            $('.lthsolr_publication_row').on( 'click', function () {
                if(lth_solr_detailpage) {
                    var id = $(this).attr('id');
                    //console.log(id);
                    window.location.href = lth_solr_detailpage + '?no_cache=1&uuid=' + id;
                }
            });
            
        }

    });
    
}


function listPublications_old()
{
    var syslang = $('#lth_solr_syslang').val();
    var dt = $('#lthsolr_table').DataTable({
        language: {
            url: 'typo3conf/ext/lth_solr/res/datatables_' + syslang + '.json'
        },
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
    $('#lthsolr_table tbody').on( 'click', 'tr', function () {
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


function showPublication()
{
    var lth_solr_staffdetailpage = $('#lth_solr_staffdetailpage').val();
    var lth_solr_projectdetailpage = $('#lth_solr_projectdetailpage').val();
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'showPublication',
            scope : $('#lth_solr_uuid').val(),
            syslang : $('#lth_solr_syslang').val(),
            detailPage: lth_solr_staffdetailpage + ',' + lth_solr_projectdetailpage,
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: 'json',
        beforeSend: function () {
            $('#lth_solr_container').html('<img src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            if(d.data) {
                var template = $('#solrTemplate').html();
                    
                template = template.replace('###abstract###', checkData(d.data[0]));
                template = template.replace('###authors###', checkData(d.data[1], lth_solr_messages.authors));
                template = template.replace('###organisations###', checkData(d.data[2], lth_solr_messages.organisations));
                template = template.replace('###externalOrganisations###', checkData(d.data[3]));
                template = template.replace('###language###', checkData(d.data[4], lth_solr_messages.language));
                template = template.replace('###pages###', checkData(d.data[5], lth_solr_messages.pages));
                template = template.replace('###numberOfPages###', checkData(d.data[6], lth_solr_messages.numberOfPages));
                template = template.replace('###volume###', checkData(d.data[7]));
                template = template.replace('###journalNumber###', checkData(d.data[8]));
                template = template.replace('###publicationStatus###', checkData(d.data[9], lth_solr_messages.publicationStatus));
                template = template.replace('###peerReview###', checkData(d.data[10], lth_solr_messages.peerReview));
                
                $('#page_title h1').text(d.title);
                $('#lth_solr_container').html(template);
            }
        }
    });
}


function listProjects(tableStart)
{
    var syslang = $('#lth_solr_syslang').val();
    var scope = $('#lth_solr_scope').val();
    var tableLength = $('#lth_solr_no_items').val();
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'listProjects',
            table_start: tableStart,
            table_length : tableLength,
            pid : $('#pid').val(),
            pageid : $('body').attr('id'),
            scope : scope,
            syslang : syslang,
            addPeople : $('#addPeople').val(),
            sid : Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            if(tableStart > 0) {
                $('#lth_solr_projects_container').append('<img id="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
            }
            //$('#lthsolr_all').remove();
            $('#lthsolr_more').replaceWith('<img id="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            if(d.data) {
                
                $.each( d.data, function( key, aData ) {
                    var template = $('#solrProjectTemplate').html();

                    template = template.replace('###id###', aData[0]);
                    template = template.replace('###title###', aData[1]);
                    template = template.replace('###participants###', aData[2]);
                    template = template.replace('###projectStartDate###', aData[3]);
                    template = template.replace('###projectEndDate###', aData[4]);
                    template = template.replace('###projectStatus###', aData[5]);
                    
                    $('#lth_solr_projects_container').append(template);
                });
                
                $('#lthsolr_loader').remove();

                $('#lthsolr_projects_header').html('1-' + maxLength(parseInt(tableStart) + parseInt(tableLength),parseInt(d.numFound)) + ' of ' + d.numFound);
                if((parseInt(tableStart) + parseInt(tableLength)) < d.numFound) {
                    $('#lth_solr_projects_container').append('<div style="margin-top:20px;" id="lthsolr_more"><a href="javascript:" onclick="listPublications(' + (parseInt(tableStart) + parseInt(tableLength)) + ');">NEXT ' + tableLength + ' of ' + d.numFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.numFound + '); listPublications(' + (parseInt(tableStart) + parseInt(tableLength)) + ');">Show all ' + d.numFound + '</a></div>');
                }
            }
            
            $('.lthsolr_project_row').on( 'click', function () {
                var lth_solr_detailpage = $('#lth_solr_projectdetailpage').val();
                if(lth_solr_detailpage) {
                    var id = $(this).attr('id');
                    //console.log(id);
                    window.location.href = lth_solr_detailpage + '?no_cache=1&uuid=' + id;
                }
            });   
        }
    });
}

function listProjects_old()
{
    var syslang = $('#lth_solr_syslang').val();
    var dt = $('#lthsolr_table').DataTable({
        language: {
            url: 'typo3conf/ext/lth_solr/res/datatables_' + syslang + '.json'
        },        
        //"processing": true,
        //"serverSide": true,
        "ajax": "index.php?eID=lth_solr&action=listProjects&table_length=10&scope=" + $('#lth_solr_scope').val() + "&sys_language_uid=" + $('#sys_language_uid').val() + "&sid=" +  Math.random(),
        "columns": [
            { "data": "title" },
            { "data": "participants" },
            { "data": "projectStartDate" },
            { "data": "projectEndDate" },
            { "data": "projectStatus" }
        ]
    });
    $('#lthsolr_table tbody').on( 'click', 'tr', function () {
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
    var syslang = $('#lth_solr_syslang').val();
    var tableLength = $('#lth_solr_no_items').val();
    var tableStartPublications = 0;
    var tableStartProjects = 0;
    
    $.ajax({
        type : "POST",
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'showStaff',
            table_length : tableLength,
            pid : $('#pid').val(),
            pageid : $('body').attr('id'),
            scope : $('#lth_solr_scope').val(),
            syslang : syslang,
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: "json",
        beforeSend: function () {
            $('#lthsolr_staff_container').append('<img id="lthsolr_loader_staff" src="/fileadmin/templates/images/ajax-loader.gif" />');
            $('#lthsolr_publications_container').append('<img id="lthsolr_loader_publication" src="/fileadmin/templates/images/ajax-loader.gif" />');
            $('#lthsolr_projects_container').append('<img id="lthsolr_loader_project" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            //Staff
            if(d.personData) {                                 
                $.each( d.personData, function( key, aData ) {
                    var intro = '';
                    var template = $('#solrStaffTemplate').html();

                    var id = aData[15];
                    template = template.replace('###id###', id);

                    var display_name_t = aData[0] + ' ' + aData[1];
                    $('#page_title h1').text(display_name_t);
                    template = template.replace('###display_name_t###', display_name_t);
                    var title, title_t = '', title_en_t = '', oname = '', oname_t = '', oname_en_t = '', phone = '', roomNumber = '', homePage = '';

                    template = template.replace(/###email_t###/g, aData[6]);

                    //if(aData[17]) {
                       // for (i = 0; i < aData[17].length; i++) {
                           // if(inArray(scope, aData[17][i].split(','))) {
                                if(aData[2]) title_t = aData[2][0];
                                if(aData[3]) title_en_t = aData[3][0];
                                if(aData[7]) oname_t = aData[7][0];
                                if(aData[8]) oname_en_t = aData[8][0];
                                if(aData[4]) {
                                    phone = aData[4][0];
                                    
                                }
                                if(phone) phone = phone.replace('+4646222', '+46 46 222 ').replace(/(.{2}$)/, ' $1');
                                if(aData[14]) {
                                    if(phone) phone += ', ';
                                    phone += aData[14][0];
                                }

                           // }
                       // }
                    //}
                        
                    if(syslang == 'en' && title_en_t) {
                        title = title_en_t;
                    } else if(title_t) {
                        title = title_t;
                    } 

                    template = template.replace('###title_t###', titleCase(title));
                    template = template.replace('###phone_t###', phone);

                    if(syslang == 'en' && oname_en_t) {
                        oname = oname_en_t;
                    } else if(oname_t) {
                        oname = oname_t;
                    } 
                    template = template.replace('###oname_t###', oname);

                    template = template.replace('###primary_affiliation_t###', aData[9]);

                    if(aData[10]) {
                        homePage = lth_solr_messages.personal_homepage + ': <a data-homepage="' + aData[10] + '" href="' + aData[10] + '">' + aData[10] + '</a>';
                    } else if(aData[15]) {
                        homePage = '<a href="' + window.location.href + 'presentation_single_person_right?query='+aData[15]+'&action=detail&sid='+Math.random()+'">Läs mer om ' + display_name_t + '</a>';
                    }
                    template = template.replace('###homepage_t###', '<p>' + homePage + '</p>');

                    //template = template.replace('###image_t###', '<div style="height: 100px"><img style="max-height: 100%; max-width: 100%" src="' + aData[11] + '" /></div>');
                    var image = '';
                    if(aData[11]) image = '<div class="align_left" style="width:80px;"><img style="max-height: 100%; max-width: 100%" src="' + aData[11] + '" /></div>';
                    template = template.replace('###image_t###', image);
                    
                    if(aData[12]) intro = aData[12].replace('\n','<br />');
                    template = template.replace('###lth_solr_intro###', intro);

                    roomNumber = aData[13];
                    if(roomNumber) {
                        roomNumber = '(' + lth_solr_messages.room + ' ' + aData[13] + ')';
                    } else {
                        roomNumber = '';
                    }
                    template = template.replace('###room_number_s###', roomNumber);
                    ophone = aData[17];
                    ostreet = aData[18];
                    ocity = aData[19];
                    if(aData[20]) {
                        opostal_address = aData[20].split('$').join(', ');
                    }
                    template = template.replace('###visiting_address###', ostreet + ' ' + ocity);
                    template = template.replace('###postal_address###', opostal_address);
                    $('#lthsolr_staff_container').append(template);
                });
                $('#lthsolr_loader_staff').remove();
            } else {
                $('#lthsolr_loader_staff').remove();
            }

            //Publications
            //console.log(d.publicationData.length);
            if(d.publicationData.length > 0) {
                $('#lthsolr_publications_header').append('<h3>Publications</h3>');
                $.each( d.publicationData, function( key, aData ) {
                    var template = $('#solrPublicationTemplate').html();

                    template = template.replace('###id###', aData[0]);
                    template = template.replace('###title###', aData[1]);
                    template = template.replace('###authorName###', aData[2]);
                    template = template.replace('###publicationType###', aData[3]);
                    template = template.replace('###publicationDateYear###', aData[4]);
                    
                    $('#lthsolr_publications_container').append(template);
                });
                
                $('#lthsolr_loader_publication').remove();

                $('#lthsolr_publications_header').append('1-' + maxLength(parseInt(tableStartPublications) + parseInt(tableLength),parseInt(d.publicationNumFound)) + ' of ' + d.publicationNumFound);
                if((parseInt(tableStartPublications) + parseInt(tableLength)) < d.publicationNumFound) {
                    $('#lthsolr_publications_container').append('<div style="margin-top:20px;" id="lthsolr_more"><a href="javascript:" onclick="listPublications(' + (parseInt(tableStartPublications) + parseInt(tableLength)) + ');">NEXT ' + tableLength + ' of ' + d.publicationNumFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.publicationNumFound + '); listPublications(' + (parseInt(tableStartPublications) + parseInt(tableLength)) + ');">Show all ' + d.publicationNumFound + '</a></div>');
                }
            } else {
                $('#lthsolr_loader_publication').remove();
            }
            
            //Projects
            //console.log(d.projectData.length);
            if(d.projectData.length > 0) {
                $('#lthsolr_projects_header').append('<h3>Projects</h3>');
                $.each( d.projectData, function( key, aData ) {
                    var template = $('#solrProjectTemplate').html();

                    template = template.replace('###id###', aData[0]);
                    template = template.replace('###title###', aData[1]);
                    template = template.replace('###participants###', aData[2]);
                    template = template.replace('###projectStartDate###', aData[3]);
                    template = template.replace('###projectEndDate###', aData[4]);
                    template = template.replace('###projectStatus###', aData[5]);
                    
                    $('#lthsolr_projects_container').append(template);
                });
                
                $('#lthsolr_loader_project').remove();

                $('#lthsolr_projects_header').append('1-' + maxLength(parseInt(tableStartProjects) + parseInt(tableLength),parseInt(d.projectNumFound)) + ' of ' + d.projectNumFound);
                if((parseInt(tableStartProjects) + parseInt(tableLength)) < d.projectNumFound) {
                    $('#lthsolr_projects_container').append('<div style="margin-top:20px;" id="lthsolr_more"><a href="javascript:" onclick="listProjects(' + (parseInt(tableStartProjects) + parseInt(tableLength)) + ');">NEXT ' + tableLength + ' of ' + d.projectNumFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.numFound + '); listProjects(' + (parseInt(tableStartProjects) + parseInt(tableLength)) + ');">Show all ' + d.projectNumFound + '</a></div>');
                }
            } else {
                $('#lthsolr_loader_project').remove();
            }
            
            $('.lthsolr_publication_row').on( 'click', function () {
                var lth_solr_detailpage = $('#lth_solr_publicationdetailpage').val();
                if(lth_solr_detailpage) {
                    var id = $(this).attr('id');
                    //console.log(id);
                    window.location.href = lth_solr_detailpage + '?no_cache=1&uuid=' + id;
                }
            });
            $('.lthsolr_project_row').on( 'click', function () {
                var lth_solr_detailpage = $('#lth_solr_projectdetailpage').val();
                if(lth_solr_detailpage) {
                    var id = $(this).attr('id');
                    //console.log(id);
                    window.location.href = lth_solr_detailpage + '?no_cache=1&uuid=' + id;
                }
            });
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
}


String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}


function checkData(data, label)
{
    var content = '';
    if(data) {
        content = '<p>';
        if(label) content += '<b>' + label + '</b>';
        content += data + '</p>';
        return content;
    } else {
        return '';
    }
}


function createFacetClick()
{
    $('.lth_solr_facet').click(function() {
        listStaff(0, getFacets(), $('.lthsolr_filter').val().trim(),false,false);
    });
}


function getFacets()
{
    var facet = [];
    $(".lth_solr_facet_container input[type=checkbox]").each(function() {
        if($(this).prop('checked')) {
            facet.push($(this).val());
        };
    });
    if(facet.length > 0) {
        return JSON.stringify(facet);
    } else {
        return null;
    }
}

/*function lthSolrGetCookie(cname)
            url : 'http://connector.search.lu.se:8181/solr/sr/130.235.208.15/sid-/' + query + '/customsites/1/undefined?' + d.getTime() + '-sid-d86c248d60b4072f018c',
            //url: 'http://connector.search.lu.se:8181/solr/ac/130.235.208.15/sid-/' + query + '/customsites',
}*/