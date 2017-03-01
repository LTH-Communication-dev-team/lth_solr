var lthClassesToShow = Array('dt-buttons');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function() {
    
    if($('#searchSiteMain').val()) {
        //widget($('#query').val());
        searchLong($('#searchSiteMain').val(), 0, 0, 0, 0, false);
    }
    //console.log($('#lth_solr_action').val());
    if($('#lth_solr_action').val() == 'listStaff') {
        listStaff(0);
    } else if($('#lth_solr_detail_action').val() == 'showStaff') {
        showStaff();
    } else if($('#lth_solr_action').val() == 'listPublications') {
        listPublications(0);
    } else if($('#lth_solr_action').val() == 'listStudentPapers') {
        listStudentPapers(0);
    } else if($('#lth_solr_action').val() == 'listProjects') {
        listProjects(0);
    } else if($('#lth_solr_action').val() == 'showPublication') {
        showPublication();
    } else if($('#lth_solr_action').val() == 'showProject') {
        showProject();
    }
    

    $('#lthsolr_staff_filter').keyup(function() {
        var noQuery;
        if($(this).val().trim() === '') {
            noQuery = true;
        } else {
            noQuery = false;
        }
        listStaff(0, getFacets(), $(this).val().trim(), noQuery);
    });
    
    $('#lthsolr_publications_filter').keyup(function() {
        var noQuery;
        if($(this).val().trim() === '') {
            noQuery = true;
        } else {
            noQuery = false;
        }
        listPublications(0, getFacets(), $(this).val().trim(), noQuery);
    });
    
    $( "#searchSiteMain a" ).autocomplete({
        source: "index.php?eID=lth_solr&action=searchShort",
        minLength: 2,
        select: function( event, ui ) {
            //window.location = $('#searchsiteformlth').attr('action') + '?query=' + $( "#searchSiteMain" ).val();
            $('#searchSiteMain').val(ui.item.label.replace('<strong>','').replace('</strong>','').split(',').shift());
            $('#lthsolr_form').submit();
        }
    });
    
    $('#lth_solr_tools').click(function() {
        $('#lth_solr_hidden_tools').toggle('slow');
    });
    
    $('.exportStaffCsv').click(function() {
        exportStaff('csv');
    });
    
    $('.exportStaffPdf').click(function() {
        exportStaff('pdf');
    });
    
    $('.exportStaffTxt').click(function() {
        exportStaff('txt');
    });
});


function exportStaff(exportType)
{
    if(exportType==='csv') {
        var csv = $(".lthsolr_row").map(function(a,i){
            return $.trim($(this).text()).split(/\s*\n\s*/).join(",");
        }).toArray().join("\r\n");
        download(csv, "tabledata.csv", "text/csv");
    } else if(exportType==='pdf') {
        
    } else if(exportType==='txt') {
        var txt = $(".lthsolr_row").map(function(a,i){
            return $.trim($(this).text()).split(/\s*\n\s*/).join(",");
        }).toArray().join("\r\n");
        download(txt, "tabledata.txt", "text/txt");
    }
}


function exportTableToCSV($table, filename) {

    var $rows = $table.find('tr:has(td)'),

      // Temporary delimiter characters unlikely to be typed by keyboard
      // This is to avoid accidentally splitting the actual contents
      tmpColDelim = String.fromCharCode(11), // vertical tab character
      tmpRowDelim = String.fromCharCode(0), // null character

      // actual delimiter characters for CSV format
      colDelim = '","',
      rowDelim = '"\r\n"',

      // Grab text from table into CSV formatted string
      csv = '"' + $rows.map(function(i, row) {
        var $row = $(row),
          $cols = $row.find('td');

        return $cols.map(function(j, col) {
          var $col = $(col),
            text = $col.text();

          return text.replace(/"/g, '""'); // escape double quotes

        }).get().join(tmpColDelim);

      }).get().join(tmpRowDelim)
      .split(tmpRowDelim).join(rowDelim)
      .split(tmpColDelim).join(colDelim) + '"';

    // Deliberate 'false', see comment below
    if (false && window.navigator.msSaveBlob) {

      var blob = new Blob([decodeURIComponent(csv)], {
        type: 'text/csv;charset=utf8'
      });

      // Crashes in IE 10, IE 11 and Microsoft Edge
      // See MS Edge Issue #10396033
      // Hence, the deliberate 'false'
      // This is here just for completeness
      // Remove the 'false' at your own risk
      window.navigator.msSaveBlob(blob, filename);

    } else if (window.Blob && window.URL) {
      // HTML5 Blob        
      var blob = new Blob([csv], {
        type: 'text/csv;charset=utf-8'
      });
      var csvUrl = URL.createObjectURL(blob);

      $(this)
        .attr({
          'download': filename,
          'href': csvUrl
        });
    } else {
      // Data URI
      var csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);

      $(this)
        .attr({
          'download': filename,
          'href': csvData,
          'target': '_blank'
        });
    }
  }


function listStaff(tableStart, facet, query, noQuery, more)
{
    var syslang = $('#lth_solr_syslang').val();
    var scope = $('#lth_solr_scope').val();
    var tableLength = $('#lth_solr_no_items').val();
    var curI;
    //var lth_solr_detailpage = $('#lth_solr_staffdetailpage').val();
    //console.log(scope);
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
            //addPeople : $('#addPeople').val(),
            facet: facet,
            sid : Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            //if((facet || query || noQuery) && (!more)) {
            if(!more) {
                $('#lthsolr_staff_container div').remove().append('<img class="lthsolr_loader" src="/fileadmin/templates/images/ajax-loader.gif" />');
            }
            //$('#lthsolr_all').remove();
            $('.lthsolr_more').replaceWith('<img class="lthsolr_loader" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            if(d.data) {
                var i = 0;
                var maxClass = '';
                var more = '';
                var count = '';
                var facet = '';
                var content = '';
                var more = '<p class="maxlist-more"></p>';
                
                if(d.facet) {
                    if($('.lth_solr_facet').length == 0) {
                        $.each( d.facet, function( key, value ) {
                            $.each( value, function( key1, value1 ) {
                                if(i > 4) {
                                    maxClass = 'maxlist-hidden ';
                                    more = '<p class="maxlist-more"><a href="#">' + lth_solr_messages.show_all + '</a></p>';
                                }

                                facet = value1[0];
                                count = value1[1];
                                if(parseInt(value1[1]) > 0) {
                                    //content += '<li' + maxClass + '>';
                                    content += '<label class="' + maxClass + 'checkbox-inline">';
                                    content += '<input type="checkbox" class="lth_solr_facet" name="lth_solr_facet" value="' + key.split('$').shift() + '###' + facet.split('$').shift() + '">';
                                    content += facet.split('$').shift().capitalize().replace(/_/g, ' ') + ' [' + count + '] ';
                                    //content += '</li>';
                                    content += '</label>';
                                }
                                i++;
                            });
                            
                            //$('.lth_solr_facet_container').append('<div class="item-list"><ul><li>' + content + '</ul>' + more + '</div><p></p>');
                            $('.lth_solr_facet_container').append(content + more);
                            i=0;
                            maxClass='';
                            more='';
                            content = '';
                        });
                        createFacetClick('listStaff');
                        toggleFacets();
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

                    i=0;
                    curI = 0;
                    for (i = 0; i < aData[16].length; i++) {
                        if(scope===aData[16][i]) {
                            curI = i;
                        }
                    }
                    //if(aData[17]) {
                        //
                            //if(inArray(scope, aData[17][i].split(','))) {
                    if(aData[2]) title_t = aData[2][curI];
                    if(aData[3]) title_en_t = aData[3][curI];
                    if(aData[7]) oname_t = aData[7][curI];
                    if(aData[8]) oname_en_t = aData[8][curI];
                    if(aData[4]) {
                        if(aData[4][curI]) {
                            phone = aData[4][curI];
                        } else {
                            phone = aData[4][0];
                        }

                    }
                    if(phone) phone = phone.replace('+4646222', '+46 46 222 ').replace(/(.{2}$)/, ' $1');
                    if(aData[14]) {
                        if(phone) phone += ', ';
                        phone += '+46 ' + aData[14][0].replace(/ /g, '').replace('+46','').replace(/(\d{2})(\d{3})(\d{2})(\d{2})/, "$1 $2 $3 $4");;
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
                        homePage = lth_solr_messages.personal_homepage + ': <a data-homepage="' + aData[15] + '" href="' + aData[15] + '">' + aData[15] + '</a>';
                    }
                    template = template.replace('###homepage_t###', '<p>' + homePage + '</p>');

                    var image = '';
                    if(aData[11]) image = '<div class="align_left" style="height:100px;"><img style="max-height: 100%; max-width: 100%" src="' + aData[11] + '" /></div>';
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
                $('.lthsolr_loader').remove();
                
                $('#lthsolr_staff_header').html('1-' + maxLength(parseInt(tableStart), parseInt(tableLength), parseInt(d.numFound)) + ' ' + lth_solr_messages.of + ' ' + d.numFound);
                if((parseInt(tableStart) + parseInt(tableLength)) < d.numFound) {
                    $('#lthsolr_staff_container').append('<div style="margin-top:20px;" class="lthsolr_more"><a href="javascript:" onclick="listStaff(' + (parseInt(tableStart) + parseInt(tableLength)) + ',getFacets(),$(\'#lthsolr_staff_filter\').val().trim(),false,true);">' + lth_solr_messages.next + ' ' + remain(parseInt(tableStart),parseInt(tableLength),parseInt(d.numFound)) + ' ' + lth_solr_messages.of + ' ' + d.numFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.numFound + '); listStaff(' + (parseInt(tableStart) + parseInt(tableLength)) + ',getFacets(),$(\'#lthsolr_staff_filter\').val().trim(),false,true);">' + lth_solr_messages.show_all + ' ' + d.numFound + '</a></div>');
                }
            }
            
           /* $('.lthsolr_row').on( 'click', function () {
                //console.log(lth_solr_detailpage);
                if(lth_solr_detailpage) {
                    var id = $(this).attr('id');
                    //console.log(id);
                    if($(this).find('[data-homepage]').attr('href')) {
                        window.location.href = $(this).find('[data-homepage]').attr('href');
                    } else {
                        //window.location.href = lth_solr_detailpage + '?no_cache=1&uuid=' + id;
                        var url = lth_solr_detailpage;
                        var form = $('<form action="' + url + '" method="post">' +
                          '<input type="text" name="uuid" value="' + id + '" />' +
                          '<input type="text" name="no_cache" value="1" />' +
                          '</form>');
                        $('body').append(form);
                        form.submit();
                        ////
                    }
                }
            });*/
            
        }
    });
}


function maxLength(tableStart, tableLength, numFound)
{
    console.log(tableStart + ';' + tableLength + ';' + numFound);
    if(tableStart + tableLength > numFound) {
        return numFound;
    } else {
        return tableStart + tableLength;
    }
}


function remain(tableStart, tableLength, numFound)
{
    //console.log(tableStart + ';' + tableLength + ';' + numFound);
    if((tableStart + tableLength + tableLength) > numFound) {
        return numFound - (tableStart + tableLength);
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


/*function showMore()
{
    var readMoreText = $('.readmore').text();
    var scrollHeight = '';
    if(readMoreText === lth_solr_messages.more) {
        scrollHeight = $('.more-content')[0].scrollHeight;
        $('.readmore').text(lth_solr_messages.close);
    } else {
        scrollHeight = '80';
        $('.readmore').text(lth_solr_messages.more);
    }
    $('.more-content').css('height',scrollHeight+'px');
}*/


function searchLong(term, startPeople, startPages, startDocuments, startCourses, more)
{
    if(term.replace('"','').length < 2) return false;
    var syslang = $('#lth_solr_syslang').val();
    var tableLength = 8; //$('#lth_solr_no_items').val();
    var template;
    //console.log(more);
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'searchLong',
            term : term,
            peopleOffset: startPeople,
            pageOffset: startPages,
            documentOffset: startDocuments,
            courseOffset: startCourses,
            table_length : tableLength,
            more: more,
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: 'json',
        beforeSend: function () {
            if(startPeople > 0 || more === 'people') $('#lthsolr_staff_container').html('<img class="lthsolr_loader" id="lthsolr_loader_staff" src="/fileadmin/templates/images/ajax-loader.gif" />');
            if(startPages > 0 || more === 'pages') $('#lthsolr_pages_container').html('<img class="lthsolr_loader" id="lthsolr_loader_pages" src="/fileadmin/templates/images/ajax-loader.gif" />');
            if(startDocuments > 0 || more === 'documents') $('#lthsolr_documents_container').html('<img class="lthsolr_loader" id="lthsolr_loader_documents" src="/fileadmin/templates/images/ajax-loader.gif" />');
            if(startCourses > 0 || more === 'courses') $('#lthsolr_courses_container').html('<img class="lthsolr_loader" id="lthsolr_loader_courses" src="/fileadmin/templates/images/ajax-loader.gif" />');
            
            //$('.lthsolr_more').replaceWith('<img class="lthsolr_loader" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            var i = 0;
            var maxClass = '';
            var more = '';
            var count = '';
            var facet = '';
            var content = '';
            var id, title, teaser, url, link;

            //if(peopleOffset == 0 && pageOffset == 0 && documentOffset == 0) $('#lthsolr_staff_container').html('');
            $('#content_navigation').append('<div class="lth_solr_left_nav"><ul class="lth_solr_res"></ul></div>');
            
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

            var tableCounter = 4;
            if(d.peopleData.length > 0) {
                $('.lth_solr_res').append('<li>People</li>');
                $.each( d.peopleData, function( key, aData ) {
                    var intro = '';
                    template = $('#solrTemplate').html();

                    var id = aData[5];
                    template = template.replace('###id###', id);

                    var display_name = aData[0] + ' ' + aData[1];

                    template = template.replace('###display_name_t###', display_name);
                    var title, title_t = '', title_en_t = '', oname = '', oname_t = '', oname_en_t = '', phone = '', roomNumber = '', homePage = '';

                    template = template.replace('###email_t###', aData[6]);
                    
                    if(aData[2]) title_t = splitString(aData[2][0]+'',30);
                    if(aData[3]) title_en_t = splitString(aData[3][0]+'',30);
                    if(aData[7]) oname_t = splitString(aData[7][0]+'',30);
                    if(aData[8]) oname_en_t = splitString(aData[8][0]+'',30);
                    if(aData[4]) {
                        phone = aData[4][0];
                    }
                    if(phone) phone = phone.replace('+4646222', '+46 46 222 ').replace(/(.{2}$)/, ' $1');
                    if(aData[12]) {
                        if(phone) phone += ', ';
                        phone += aData[12][0];
                    }
                    //if(phone) phone = $(phone).wrap('<p class="person-phone"></p>').toString();
                        
                    if(syslang == 'en' && title_en_t) {
                        title = title_en_t;
                    } else if(title_t) {
                        title = title_t;
                    } 

                    if(!title || title == '') {
                        title = 'No title'
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

                    /*if(aData[10]) {
                        homePage = lth_solr_messages.personal_homepage + ': <a data-homepage="' + aData[10] + '" href="' + aData[10] + '">' + aData[10] + '</a>';
                    } else if(aData[15]) {
                        homePage = lth_solr_messages.read_more_about + ' ' + display_name_t;
                    }
                    template = template.replace('###homepage_t###', '<p>' + homePage + '</p>');
                    */
                    template = template.replace('###homepage_t###', '');

                    //template = template.replace('###image_t###', '<div style="height: 100px"><img style="max-height: 100%; max-width: 100%" src="' + aData[11] + '" /></div>');
                    var image = '';
                    if(aData[11] != 0 && aData[11] != null) {
                       image = aData[11];
                    } else if(aData[12]) {
                       image = aData[12];
                    }

                    if(image !== '') image = '<div class="align_left" style="width:80px;"><img style="max-height: 100%; max-width: 100%" src="' + image + '" /></div>';
                    template = template.replace('###image###', image);
                    
                    //if(aData[12]) intro = aData[12].replace('\n','<br />');
                    //template = template.replace('###lth_solr_intro###', intro);

                    if(aData[13]) {
                        roomNumber = '(' + lth_solr_messages.room + ' ' + aData[11] + ')';
                    } else {
                        roomNumber = '';
                    }
                    template = template.replace('###room_number_s###', roomNumber);
                    if(tableCounter === 4) {
                        $('#lthsolr_staff_container').append('<tr></tr>');
                        tableCounter = 0
                    }
                    $('#lthsolr_staff_container tr:last').append(template);
                    tableCounter++;
                });
                $('#lthsolr_loader_staff').remove();
 
                $('#lthsolr_people_header').html('<span class="lth_solr_search_header">' + lth_solr_messages.people + '</span><span>' + (parseInt(startPeople) + 1) + '-' + maxLength(parseInt(startPeople) + parseInt(tableLength),parseInt(d.peopleNumFound)) + ' ' + lth_solr_messages.of + ' '  + d.peopleNumFound + '</span>');
                
                if((parseInt(startPeople) - parseInt(tableLength)) >= 0) {
                    $('#lthsolr_people_header').append('<span class="lthsolr_more">\n\
                    <a href="javascript:" onclick="var board_h = $(this).closest(\'.lthsolr_table_wrapper\').outerHeight();\n\
                    $(this).closest(\'.lthsolr_table_wrapper\').css(\'height\', board_h + \'px\');\n\
                    $(\'#lth_solr_no_items\').val(' + d.peopleNumFound + '); \n\
                    searchLong(\'' + term + '\',' + (parseInt(startPeople) - parseInt(tableLength)) + ',0,0,0,\'people\');">\n\
                    <span class="fa fa-angle-double-left"></span>' + lth_solr_messages.prev + '</a></span>');
                }
                if((parseInt(startPeople) + parseInt(tableLength)) < d.peopleNumFound) {
                    /*$('#lthsolr_staff_container').append('<div class="lthsolr_more"><a href="javascript:" \
                        onclick="searchLong(\'' + term + '\',' + (parseInt(startPeople) + parseInt(tableLength)) + ',0,0,true);">' + lth_solr_messages.next + ' ' + 
                        tableLength + ' ' + lth_solr_messages.of + ' ' + d.peopleNumFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + 
                        d.peopleNumFound + '); searchLong(\'' + term + '\',' +
                        (parseInt(startPeople) + parseInt(tableLength)) + ',0,0,true);">' + lth_solr_messages.show_all + ' ' + d.peopleNumFound + '</a></div>');
                    */
                    $('#lthsolr_people_header').append('<span class="lthsolr_more">\n\
                        <a href="javascript:" onclick="var board_h = $(this).closest(\'.lthsolr_table_wrapper\').outerHeight();\n\
                        $(this).closest(\'.lthsolr_table_wrapper\').css(\'height\', board_h + \'px\');\n\
                        $(\'#lth_solr_no_items\').val(' + d.peopleNumFound + ');\n\
                        searchLong(\'' + term + '\',' + (parseInt(startPeople) + parseInt(tableLength)) + ',0,0,0,\'people\');">' + lth_solr_messages.next + 
                        '<span class="fa fa-angle-double-right"></span></a></span>');
                }
            } else if(more !== true) {
                /*$('#lthsolr_loader_staff').remove();
                if(d.pageData.length == 0 && d.documentData.length == 0) {
                    $('#lthsolr_people_header').html('<h3>' + lth_solr_messages.nohits.replace('###term###',term) + '</h3>');
                }*/
                $('#lthsolr_staff_container').parent().remove();
            }
            
            var tableCounter = 4;
            var indexCounter = 1;
            if(d.pageData.length > 0) {
                $('.lth_solr_res').append('<li>Pages</li>');
                $.each( d.pageData, function( key, aData ) {
                    template = $('#solrPagesTemplate').html();
                    id = '';
                    title = '';
                    teaser = '';
                    url = '';
                    link = '';
                    
                    if(aData[0]) id = aData[0];
                    if(aData[1]) title = aData[1][0].split('|').pop().trim();
                    //console.log(title);
                    if(aData[2]) teaser = '<p>' + aData[2] + '</p>';
                    /*if(aData[3]) {
                        url = aData[3];
                        link = '<p class="pagelink"><a href="' + aData[3] + '">' + aData[3] + '</a><p>';
                    }*/

                    template = template.replace('###id###', id);
                    template = template.replace('###title###', indexCounter + '. ' + splitString(title+'',30));
                    template = template.replace('###teaser###', teaser);
                    template = template.replace('###url###', url);
                    template = template.replace('###link###', link);
                    
                    //$('#lthsolr_pages_container').append(template);
                    
                    if(tableCounter === 4) {
                        $('#lthsolr_pages_container').append('<tr></tr>');
                        tableCounter = 0
                    }
                    $('#lthsolr_pages_container tr:last').append(template);
                    tableCounter++;
                    indexCounter++;
                });
                
                $('#lthsolr_loader_pages').remove();

                $('#lthsolr_pages_header').html('<span class="lth_solr_search_header">'+ lth_solr_messages.webpages + '</span><span>' + (parseInt(startPeople) + 1) + '-' + maxLength(parseInt(startPages) + parseInt(tableLength),parseInt(d.pageNumFound)) + ' ' + lth_solr_messages.of + ' '  + d.pageNumFound + '</span>');
                
                if((parseInt(startPages) - parseInt(tableLength)) >= 0) {
                    $('#lthsolr_pages_header').append('<span class="lthsolr_more">\n\
                    <a href="javascript:" onclick="var board_h = $(this).closest(\'.lthsolr_table_wrapper\').outerHeight();\n\
                    $(this).closest(\'.lthsolr_table_wrapper\').css(\'height\', board_h + \'px\');\
                    $(\'#lth_solr_no_items\').val(' + d.pageNumFound + '); \n\
                    searchLong(\'' + term + '\',0,' + (parseInt(startPages) - parseInt(tableLength)) + ',0,0,\'pages\');">\n\
                    <span class="fa fa-angle-double-left"></span>' + lth_solr_messages.prev + '</a></span>');
                }
                if((parseInt(startPages) + parseInt(tableLength)) < d.pageNumFound) {
                    /*$('#lthsolr_pages_container').append('<div style="margin-top:20px;" class="lthsolr_more"><a href="javascript:" \n\
                        onclick="searchLong(\'' + term + '\',0,' + (parseInt(startPages) + parseInt(tableLength)) + ',0,true);">' + lth_solr_messages.next + ' ' + tableLength + ' ' + 
                            lth_solr_messages.of + ' '  + d.pageNumFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.pageNumFound + '); \
                            searchLong(\'' + term + '\',0,' + (parseInt(startPages) + parseInt(tableLength)) + ',0,true);">' + lth_solr_messages.show_all + ' ' + d.pageNumFound + '</a></div>');
                    */
                   $('#lthsolr_pages_header').append('<span class="lthsolr_more">\n\
                        <a href="javascript:" onclick="var board_h = $(this).closest(\'.lthsolr_table_wrapper\').outerHeight();\n\
                        $(this).closest(\'.lthsolr_table_wrapper\').css(\'height\', board_h + \'px\');\n\
                        $(\'#lth_solr_no_items\').val(' + d.pageNumFound + '); \
                        searchLong(\'' + term + '\',0,' + (parseInt(startPages) + parseInt(tableLength)) + ',0,0,\'pages\');">\n\
                        <span class="fa fa-angle-double-right"></span>' + lth_solr_messages.next + '</a></span>');
                }
            } else if(more !== true) {
                //$('#lthsolr_loader_pages').remove();
                $('#lthsolr_pages_container').parent().remove();
            }
            
            var tableCounter = 4;
            var indexCounter = 1;
            if(d.documentData.length > 0) {
                $('.lth_solr_res').append('<li>Documents</li>');
                $.each( d.documentData, function( key, aData ) {
                    template = $('#solrPagesTemplate').html();
                    id = '';
                    title = '';
                    teaser = '';
                    url = '';
                    link = '';
                    
                    if(aData[0]) id = aData[0];
                    if(aData[1]) title = aData[1];
                    if(aData[2]) teaser = '<p>' + aData[2] + '</p>';
                    if(aData[3]) {
                        url = aData[3];
                        link = '<p><a href="' + aData[3].toString().substring(0,25) + '">' + aData[3].toString().substring(0,25) + '</a><p>';
                    }

                    template = template.replace('###id###', id);
                    template = template.replace('###title###', indexCounter + '. ' + splitString(title+'',25));
                    template = template.replace('###teaser###', teaser);
                    //template = template.replace('###url###', url);
                    template = template.replace('###link###', link);
                    
                    //$('#lthsolr_documents_container').append(template);
                    if(tableCounter === 4) {
                        $('#lthsolr_documents_container').append('<tr></tr>');
                        tableCounter = 0
                    }
                    $('#lthsolr_documents_container tr:last').append(template);
                    tableCounter++;
                    indexCounter++;
                });
                
                $('#lthsolr_loader_documents').remove();

                $('#lthsolr_documents_header').html('<span class="lth_solr_search_header">'+ lth_solr_messages.documents + '</span><span>' + (parseInt(startPeople) + 1) + '-' + maxLength(parseInt(startDocuments) + parseInt(tableLength),parseInt(d.documentNumFound)) + ' ' + lth_solr_messages.of + ' '  + d.documentNumFound + '</span>');
                if((parseInt(startDocuments) - parseInt(tableLength)) >= 0) {
                    $('#lthsolr_documents_header').append('<span class="lthsolr_more">\n\
                    <a href="javascript:" onclick="var board_h = $(this).closest(\'.lthsolr_table_wrapper\').outerHeight(); \n\
                    $(this).closest(\'.lthsolr_table_wrapper\').css(\'height\', board_h + \'px\');\n\
                    $(\'#lth_solr_no_items\').val(' + d.documentNumFound + '); \n\
                    searchLong(\'' + term + '\',0,0,' + (parseInt(startDocuments) - parseInt(tableLength)) + ',0,\'documents\',0);">\n\
                    <span class="fa fa-angle-double-left"></span>' + lth_solr_messages.prev + '</a></span>');
                }
                if((parseInt(startDocuments) + parseInt(tableLength)) < d.documentNumFound) {
                    /*$('#lthsolr_documents_container').append('<div class="lthsolr_more"><a href="javascript:" \n\
                        onclick="searchLong(\'' + term + '\',0,0,' + (parseInt(startPages) + parseInt(tableLength)) + ',true);">' + lth_solr_messages.of + ' ' + tableLength + ' ' + 
                            lth_solr_messages.next + ' '  + d.documentNumFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.documentsNumFound + '); \
                            searchLong(\'' + term + '\',0,0,' + (parseInt(startPages) + parseInt(tableLength)) + ',true);">' + lth_solr_messages.show_all + ' ' + d.documentNumFound + '</a></div>');
                    */
                    $('#lthsolr_documents_header').append('<span class="lthsolr_more">\n\
                        <a href="javascript:" onclick="var board_h = $(this).closest(\'.lthsolr_table_wrapper\').outerHeight();\n\
                        $(this).closest(\'.lthsolr_table_wrapper\').css(\'height\', board_h + \'px\');\n\
                        $(\'#lth_solr_no_items\').val(' + d.documentsNumFound + '); \
                        searchLong(\'' + term + '\',0,0,' + (parseInt(startDocuments) + parseInt(tableLength)) + ',0,\'documents\');">' + lth_solr_messages.next + 
                        '<span class="fa fa-angle-double-right"></span></a></span>');
                }
            } else if(more !== true) {
                //$('#lthsolr_loader_documents').remove();
                $('#lthsolr_documents_container').parent().remove();
            }
                
            var tableCounter = 4;
            var indexCounter = 1;
            var course_code, credit;
            if(d.courseData.length > 0) {
                $('.lth_solr_res').append('<li>Courses</li>');
                $.each( d.courseData, function( key, aData ) {
                    template = $('#solrCoursesTemplate').html();
                    id = '';
                    title = '';
                    course_code = '';
                    url = '';
                    link = '';
                    credit = '';

                    if(aData[0]) id = aData[0];
                    if(aData[1]) title = aData[1];
                    if(aData[3]) course_code = aData[3];
                    if(aData[4]) credit = aData[4] + 'hp';
                    if(aData[5]) {
                        url = aData[5].toString();
                        if(url.substr(0,4) != 'http') {
                            url = 'http://' + url;
                        }
                        link = '<p><a href="' + url + '">' + url.substring(0,25) + '</a><p>';
                    }

                    template = template.replace('###id###', id);
                    template = template.replace('###title###', indexCounter + '. ' + splitString(title+'',30));
                    template = template.replace('###course_code###', course_code);
                    template = template.replace('###link###', link);
                    template = template.replace('###credit###', credit);
                    
                    //$('#lthsolr_documents_container').append(template);
                    if(tableCounter === 4) {
                        $('#lthsolr_courses_container').append('<tr></tr>');
                        tableCounter = 0
                    }
                    $('#lthsolr_courses_container tr:last').append(template);
                    tableCounter++;
                    indexCounter++;
                });

                //$('#lthsolr_people_header').html('<div class="lth_solr_search_header">1-' + maxLength(parseInt(startPeople) + parseInt(tableLength),parseInt(d.peopleNumFound)) + ' träffar ' + lth_solr_messages.of + ' '  + d.peopleNumFound + ' på "'+term+'" i kategorin ' + lth_solr_messages.pages + '</div>');
                $('#lthsolr_courses_header').html('<span class="lth_solr_search_header">'+ lth_solr_messages.courses + '</span><span>' + (parseInt(startCourses) + 1) + '-' + maxLength(parseInt(startCourses) + parseInt(tableLength),parseInt(d.courseNumFound)) + ' ' + lth_solr_messages.of + ' '  + d.courseNumFound + '</span>');
                
                if((parseInt(startCourses) - parseInt(tableLength)) >= 0) {
                    $('#lthsolr_courses_header').append('<span class="lthsolr_more">\n\
                        <a href="javascript:" onclick="var board_h = $(this).closest(\'.lthsolr_table_wrapper\').height(); \n\
                        $(this).closest(\'.lthsolr_table_wrapper\').css(\'outerHeight\', board_h + \'px\');\n\
                        $(\'#lth_solr_no_items\').val(' + d.documentNumFound + '); \n\
                        searchLong(\'' + term + '\',0,0,0,' + (parseInt(startCourses) - parseInt(tableLength)) + ',\'courses\');">\n\
                        <span class="fa fa-angle-double-left"></span>' + lth_solr_messages.prev + '</a></span>');
  
                }
                if((parseInt(startCourses) + parseInt(tableLength)) < d.courseNumFound) {
                    /*$('#lthsolr_documents_container').append('<div class="lthsolr_more"><a href="javascript:" \n\
                        onclick="searchLong(\'' + term + '\',0,0,' + (parseInt(startPages) + parseInt(tableLength)) + ',true);">' + lth_solr_messages.of + ' ' + tableLength + ' ' + 
                            lth_solr_messages.next + ' '  + d.documentNumFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.documentsNumFound + '); \
                            searchLong(\'' + term + '\',0,0,' + (parseInt(startPages) + parseInt(tableLength)) + ',true);">' + lth_solr_messages.show_all + ' ' + d.documentNumFound + '</a></div>');
                    */
                    $('#lthsolr_courses_header').append('<span class="lthsolr_more">\n\
                        <a href="javascript:" onclick="var board_h = $(this).closest(\'.lthsolr_table_wrapper\').height();\n\
                        $(this).closest(\'.lthsolr_table_wrapper\').css(\'outerHeight\', board_h + \'px\');\n\
                        $(\'#lth_solr_no_items\').val(' + d.courseNumFound + '); \
                        searchLong(\'' + term + '\',0,0,0,' + (parseInt(startCourses) + parseInt(tableLength)) + ',\'courses\');">' + lth_solr_messages.next + 
                        '<span class="fa fa-angle-double-right"></span></a></span>');
    
                }
                $('#lthsolr_loader_courses').remove();
            } else if(more !== true) {
                $('#lthsolr_courses_container').parent().remove();
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


function listPublications(tableStart, facet, query, noQuery, more)
{
    var syslang = $('#lth_solr_syslang').val();
    var scope = $('#lth_solr_scope').val();
    var tableLength = $('#lth_solr_no_items').val();
    var lth_solr_detailpage = $('#lth_solr_publicationdetailpage').val();
    var i = 0;
    var maxClass = '';
    var count = '';
    var content = '';
    var pages, title, publicationDate, journalTitle;
    
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
            query: query,
            addPeople : $('#addPeople').val(),
            categories : $('#lth_solr_categories').val(),
            selection : $('#lth_solr_selection').val(),
            facet: facet,
            sid : Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            //   console.log(facet + ',' + query + ',' + noQuery + ', ' + more);
            if(!more) {
                $('#lthsolr_publications_container div').remove().append('<img class="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
            }

            //$('#lthsolr_all').remove();
            $('.lthsolr_more').replaceWith('<img class="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            if(d.data) {
                if(d.facet) {
                    if($('.item-list').length == 0) {
                        $.each( d.facet, function( key, value ) {
                            $.each( value, function( key1, value1 ) {
                                if(i > 4) {
                                    maxClass = ' class="maxlist-hidden"';
                                    more = '<p class="maxlist-more"><span class="glyphicon glyphicon-menu-right" aria-hidden="true"></span><a href="#">' + lth_solr_messages.more + '</a></p>';
                                }

                                facet = value1[0];
                                count = value1[1];
                                if(parseInt(value1[1]) > 0 && value1[0]) {
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
                        createFacetClick('listPublications');
                    }
                }

                $.each( d.data, function( key, aData ) {
                    var template = $('#solrPublicationTemplate').html();
                    pages = '';
                    publicationDate = '';
                    journalTitle = '';
                     if(aData[1]) {
                        title = aData[1].charAt(0).toUpperCase() + aData[1].slice(1).toLowerCase();
                    } else {
                        title = 'untitled';
                    }
                    if(lth_solr_detailpage) {
                        title = '<a href="' + lth_solr_detailpage + '?no_cache=1&uuid=' + aData[0] + '">' + title.charAt(0).toUpperCase() + title.slice(1).toLowerCase(); + '</a>';
                    }
                    if(aData[4]) publicationDate = aData[4];
                    if(aData[5]) publicationDate += '-'+aData[5];
                    if(aData[6]) publicationDate += '-'+aData[6];
                    if(aData[7]) {
                        if(syslang=='en') {
                            pages = 'p. ' + aData[7];
                        } else {
                            pages = 's. ' + aData[7];
                        }
                    }
                    if(aData[8]) {
                        if(syslang=='en') {
                            journalTitle = 'In: ' + aData[8];
                        } else {
                            journalTitle = 'I: ' + aData[8];
                        }
                    }
                    if(aData[8] && aData[9]) journalTitle += ' ' + aData[9];

                    template = template.replace('###id###', aData[0]);
                    template = template.replace('###title###', title);
                    template = template.replace('###authorName###', aData[2]);
                    template = template.replace('###publicationType###', aData[3]);
                    template = template.replace('###publicationDate###', publicationDate);
                    template = template.replace('###pages###', pages);
                    template = template.replace('###journalTitle###', journalTitle);

                    $('#lthsolr_publications_container').append(template);
                });
                
                $('.lthsolr_loader').remove();

                $('#lthsolr_publications_header').html('1-' + maxLength(parseInt(tableStart) + parseInt(tableLength),parseInt(d.numFound)) + ' ' + lth_solr_messages.of + ' ' + d.numFound);
                if((parseInt(tableStart) + parseInt(tableLength)) < d.numFound) {
                    $('#lthsolr_publications_container').append('<div style="margin-top:20px;" class="lthsolr_more"><a href="javascript:" onclick="listPublications(' + (parseInt(tableStart) + parseInt(tableLength)) + ');">' + lth_solr_messages.next + ' ' + tableLength + ' ' + lth_solr_messages.of + ' ' + d.numFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.numFound + '); listPublications(' + (parseInt(tableStart) + parseInt(tableLength)) + ');">' + lth_solr_messages.show_all + ' ' + d.numFound + '</a></div>');
                }
            }
            
            /*$('.lthsolr_publication_row').on( 'click', function () {
                if(lth_solr_detailpage) {
                    var id = $(this).attr('id');
                    //console.log(id);
                    window.location.href = lth_solr_detailpage + '?no_cache=1&uuid=' + id;
                }
            });*/
            
            toggleFacets();
        }

    });
    
}


function listStudentPapers(tableStart, facet, query, noQuery, more)
{
    var syslang = $('#lth_solr_syslang').val();
    var scope = $('#lth_solr_scope').val();
    var tableLength = $('#lth_solr_no_items').val();
    var lth_solr_detailpage = $('#lth_solr_detailpage').val();
    var i = 0;
    var maxClass, more, count, content;
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'listStudentPapers',
            table_start: tableStart,
            table_length : tableLength,
            pid : $('#pid').val(),
            pageid : $('body').attr('id'),
            scope : scope,
            syslang : syslang,
            query: query,
            addPeople : $('#addPeople').val(),
            categories : $('#lth_solr_categories').val(),
            papertype : $('#lth_solr_papertype').val(),
            facet: facet,
            sid : Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            //   console.log(facet + ',' + query + ',' + noQuery + ', ' + more);
            if((facet || facet === null || query || noQuery) && (!more)) {
                $('#lthsolr_publications_container div').remove().append('<img class="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
            }

            //$('#lthsolr_all').remove();
            $('.lthsolr_more').replaceWith('<img class="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            if(d.data) {
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
                                if(parseInt(value1[1]) > 0 && value1[0]) {
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
                        createFacetClick('listPublications');
                    }
                }
                
                $.each( d.data, function( key, aData ) {
                    var template = $('#solrPublicationTemplate').html();

                    template = template.replace('###id###', aData[0]);
                    template = template.replace('###title###', aData[1]);
                    template = template.replace(/###authorName###/g, aData[2]);
                    template = template.replace(/###publicationType###/g, aData[3]);
                    template = template.replace(/###publicationDateYear###/g, aData[4]);

                    $('#lthsolr_publications_container').append(template);
                });
                
                $('.lthsolr_loader').remove();

                $('#lthsolr_publications_header').html('1-' + maxLength(parseInt(tableStart) + parseInt(tableLength),parseInt(d.numFound)) + ' of ' + d.numFound);
                if((parseInt(tableStart) + parseInt(tableLength)) < d.numFound) {
                    $('#lthsolr_publications_container').append('<div style="margin-top:20px;" class="lthsolr_more"><a href="javascript:" onclick="listPublications(' + (parseInt(tableStart) + parseInt(tableLength)) + ');">NEXT ' + tableLength + ' of ' + d.numFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.numFound + '); listPublications(' + (parseInt(tableStart) + parseInt(tableLength)) + ');">Show all ' + d.numFound + '</a></div>');
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


function showPublication()
{
    var lth_solr_staffdetailpage = $('#lth_solr_staffdetailpage').val();
    var lth_solr_projectdetailpage = $('#lth_solr_projectdetailpage').val();
    var syslang = $('#lth_solr_syslang').val();
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'showPublication',
            scope : $('#lth_solr_uuid').val(),
            syslang : syslang,
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
                template = template.replace(/###title###/g, d.title);
                template = template.replace('###abstract###', checkData(d.data[0], lth_solr_messages.abstract));
                template = template.replace(/###authors###/g, checkData(d.data[1], lth_solr_messages.authors));
                template = template.replace('###organisations###', checkData(d.data[2], lth_solr_messages.organisations));
                template = template.replace('###externalOrganisations###', checkData(d.data[3]));
                template = template.replace('###keywords###', checkData(d.data[4], lth_solr_messages.keywords));
                template = template.replace('###language###', checkData(d.data[5], lth_solr_messages.language));
                template = template.replace('###pages###', checkData(d.data[6], lth_solr_messages.pages));
                template = template.replace('###numberOfPages###', checkData(d.data[7], lth_solr_messages.numberOfPages));
                template = template.replace('###journalTitle###', checkData(d.data[8], lth_solr_messages.journalTitle));
                template = template.replace('###volume###', checkData(d.data[9], lth_solr_messages.volume));
                template = template.replace('###journalNumber###', checkData(d.data[10], lth_solr_messages.journalNumber));
                template = template.replace('###publicationStatus###', checkData(d.data[11], lth_solr_messages.publicationStatus));
                template = template.replace('###peerReview###', checkData(d.data[12], lth_solr_messages.peerReview, syslang));
                
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
                $('#lth_solr_projects_container').append('<img class="lthsolr_loader" src="/fileadmin/templates/images/ajax-loader.gif" />');
            }
            //$('#lthsolr_all').remove();
            $('.lthsolr_more').replaceWith('<img class="lthsolr_loader" src="/fileadmin/templates/images/ajax-loader.gif" />');
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
                
                $('.lthsolr_loader').remove();

                $('#lthsolr_projects_header').html('1-' + maxLength(parseInt(tableStart) + parseInt(tableLength),parseInt(d.numFound)) + ' of ' + d.numFound);
                if((parseInt(tableStart) + parseInt(tableLength)) < d.numFound) {
                    $('#lth_solr_projects_container').append('<div style="margin-top:20px;" class="lthsolr_more"><a href="javascript:" onclick="listPublications(' + (parseInt(tableStart) + parseInt(tableLength)) + ');">NEXT ' + tableLength + ' of ' + d.numFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.numFound + '); listPublications(' + (parseInt(tableStart) + parseInt(tableLength)) + ');">Show all ' + d.numFound + '</a></div>');
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


function toggleFacets()
{
    $('.maxlist-more a').on( 'click', function () {
        $('.maxlist-hidden').toggle('slow');
        if($(this).text() == lth_solr_messages.more) {
            $(this).text(lth_solr_messages.close);
        } else {
            $(this).text(lth_solr_messages.more);
        }
        return false;
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
    var lth_solr_staff_pos = $('#lth_solr_staff_pos').val();
    
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
            if(lth_solr_staff_pos=='right') {
                $('.grid-23').after('<div id="content_sidebar_wrapper" class="grid-8 omega"><div id="content_sidebar"><h2>Contact</h2></div>');
                var staffContainer = $('#lthsolr_staff_container');
                $('#content_sidebar h2').after(staffContainer);
            }
            $('#lthsolr_staff_container').append('<img class="lthsolr_loader" id="lthsolr_loader_staff" src="/fileadmin/templates/images/ajax-loader.gif" />');
            $('#lthsolr_publications_container').append('<img class="lthsolr_loader" id="lthsolr_loader_publication" src="/fileadmin/templates/images/ajax-loader.gif" />');
            $('#lthsolr_projects_container').append('<img class="lthsolr_loader" id="lthsolr_loader_project" src="/fileadmin/templates/images/ajax-loader.gif" />');
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
                    
                    template = template.replace('###display_name_t###', display_name_t);
                    var title, title_t = '', title_en_t = '', oname = '', oname_t = '', oname_en_t = '', phone = '', roomNumber = '', homePage = '';

                    template = template.replace(/###email_t###/g, aData[6]);

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
                        
                    if(syslang == 'en' && title_en_t) {
                        title = title_en_t;
                    } else if(title_t) {
                        title = title_t;
                    } 

                    //Change page main header
                    $('#page_title h1').text(display_name_t).append('<h2>'+title+'</h2>');
                    
                    template = template.replace('###title_t###', titleCase(title));
                    template = template.replace('###phone_t###', phone);

                    if(syslang == 'en' && oname_en_t) {
                        oname = oname_en_t;
                    } else if(oname_t) {
                        oname = oname_t;
                    } 
                    template = template.replace('###oname_t###', oname);

                    template = template.replace('###primary_affiliation_t###', aData[9]);

                    /*if(aData[10]) {
                        homePage = lth_solr_messages.personal_homepage + ': <a data-homepage="' + aData[10] + '" href="' + aData[10] + '">' + aData[10] + '</a>';
                    } else if(aData[15]) {
                        homePage = lth_solr_messages.read_more_about + ' ' + display_name_t;
                    }
                    template = template.replace('###homepage_t###', '<p>' + homePage + '</p>');
                    */
                    template = template.replace('###homepage_t###', '');

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
                    
                    if(aData[21]) {
                        $('#lthsolr_publications_container').prepend('<h3>Forskning</h3>' + aData[21]);
                    }
                    $('#lthsolr_staff_container').append(template);
                });
                $('#lthsolr_loader_staff').remove();
            } else {
                $('#lthsolr_loader_staff').remove();
            }

            //Publications
            //console.log(d.publicationData.length);
            var pages, publicationDate, journalTitle, title;
            if(d.publicationData.length > 0) {
                $('#lthsolr_publications_header').append('<h3>' + lth_solr_messages.publications + '</h3>');
                $.each( d.publicationData, function( key, aData ) {
                    var template = $('#solrPublicationTemplate').html();

                    pages = '';
                    publicationDate = '';
                    journalTitle = '';
                     if(aData[1]) {
                        title = aData[1];
                    } else {
                        title = 'untitled';
                    }
                    if(aData[4]) publicationDate = aData[4];
                    if(aData[5]) publicationDate += '-'+aData[5];
                    if(aData[6]) publicationDate += '-'+aData[6];
                    if(aData[7]) {
                        if(syslang=='en') {
                            pages = 'p. ' + aData[7];
                        } else {
                            pages = 's. ' + aData[7];
                        }
                    }
                    if(aData[8]) {
                        if(syslang=='en') {
                            journalTitle = 'In: ' + aData[8];
                        } else {
                            journalTitle = 'I: ' + aData[8];
                        }
                    }
                    if(aData[8] && aData[9]) journalTitle += ' ' + aData[9];

                    template = template.replace('###id###', aData[0]);
                    template = template.replace('###title###', '<b>'+title+'</b>');
                    template = template.replace('###authorName###', aData[2]);
                    template = template.replace('###publicationType###', aData[3]);
                    template = template.replace('###publicationDate###', publicationDate);
                    template = template.replace('###pages###', pages);
                    template = template.replace('###journalTitle###', journalTitle);
                    
                    $('#lthsolr_publications_container').append(template);
                });
                
                $('#lthsolr_loader_publication').remove();

                $('#lthsolr_publications_header').append('1-' + maxLength(parseInt(tableStartPublications),parseInt(tableLength),parseInt(d.publicationNumFound)) + ' of ' + d.publicationNumFound);
                if((parseInt(tableStartPublications) + parseInt(tableLength)) < d.publicationNumFound) {
                    $('#lthsolr_publications_container').append('<div style="margin-top:20px;" class="lthsolr_more">\n\
<a href="javascript:" onclick="listPublications(' + (parseInt(tableStartPublications) + parseInt(tableLength)) + ');">' + lth_solr_messages.next + ' ' + tableLength + ' ' + lth_solr_messages.of + ' ' + d.publicationNumFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.publicationNumFound + '); listPublications(' + (parseInt(tableStartPublications) + parseInt(tableLength)) + ');">' + lth_solr_messages.show_all + ' ' + d.publicationNumFound + '</a></div>');
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
                    $('#lthsolr_projects_container').append('<div style="margin-top:20px;" class="lthsolr_more"><a href="javascript:" onclick="listProjects(' + (parseInt(tableStartProjects) + parseInt(tableLength)) + ');">NEXT ' + tableLength + ' of ' + d.projectNumFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.numFound + '); listProjects(' + (parseInt(tableStartProjects) + parseInt(tableLength)) + ');">Show all ' + d.projectNumFound + '</a></div>');
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


function checkData(data, label, syslang)
{
    var content = '';
    if(data) {
        if(data=='true' && syslang=='sv') data = 'Ja';
        if(data=='true' && syslang=='en') data = 'Yes'
        if(data=='false' && syslang=='sv') data = 'Nej'
        if(data=='false' && syslang=='en') data = 'No'
        content = '<p>';
        if(label) content += '<b>' + label + '</b><br/>';
        content += data + '</p>';
        return content;
    } else {
        return '';
    }
}


function createFacetClick(listType)
{
    $('.lth_solr_facet').click(function() {
        if(listType==='listStaff') {
            listStaff(0, getFacets(), $('#lthsolr_staff_filter').val().trim(),false,false);
        } else if(listType==='listPublications') {
            listPublications(0, getFacets(), $('#lthsolr_staff_filter').val().trim(),false,false);
        }
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

function splitString(str, length) {
    var words = str.split(" ");
    for (var j = 0; j < words.length; j++) {
        var l = words[j].length;
        if (l > length) {
            var result = [], i = 0;
            while (i < l) {
                result.push(words[j].substr(i, length))
                i += length;
            }
            words[j] = result.join(" ");
        }
    }
    return words.join(" ");
}

/*function lthSolrGetCookie(cname)
            url : 'http://connector.search.lu.se:8181/solr/sr/130.235.208.15/sid-/' + query + '/customsites/1/undefined?' + d.getTime() + '-sid-d86c248d60b4072f018c',
            //url: 'http://connector.search.lu.se:8181/solr/ac/130.235.208.15/sid-/' + query + '/customsites',
}*/