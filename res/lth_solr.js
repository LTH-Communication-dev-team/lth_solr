var lthClassesToShow = Array('dt-buttons');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function() {
    
    if($('#searchSiteMain').val()) {
        //widget($('#query').val());
        searchLong($('#searchSiteMain').val(), 0, 0, 0, false);
    }
    //console.log($('#lth_solr_action').val());
    if($('#lth_solr_action').val() == 'listStaff') {
        listStaff(0);
        $(".refine").click(function(){
            $("#lth_solr_facet_container").toggle(500);
            $("#lthsolr_staff_container").toggleClass('expand', 500);
        });
    } else if($('#lth_solr_detail_action').val() == 'showStaff') {
        showStaff();
        listPublications(0,'','','publicationYear',0,'');
        $(".refine").click(function(){
            $("#lth_solr_facet_container").toggle(500);
            $("#lthsolr_publications_container").toggleClass('expand');
        });
    } else if($('#lth_solr_action').val() == 'listPublications') {
        listPublications(0,'','','publicationYear',0,'');
        $(".refine").click(function(){
            $("#lth_solr_facet_container").toggle(500);
            $("#lthsolr_publications_container").toggleClass('expand');
        });
    } else if($('#lth_solr_action').val() == 'listStudentPapers') {
        listStudentPapers(0);
        $(".refine").click(function(){
            $("#lth_solr_facet_container").toggle(500);
            $("#lthsolr_publications_container").toggleClass('expand', 500);
        });
    } else if($('#lth_solr_action').val() == 'showStudentPaper') {
        showStudentPaper(0);
    } else if($('#lth_solr_action').val() == 'listProjects') {
        listProjects(0);
    } else if($('#lth_solr_action').val() == 'showPublication') {
        showPublication();
    } else if($('#lth_solr_action').val() == 'showProject') {
        showProject();
    } else if($('#lth_solr_action').val() == 'listTagCloud') {
        listTagCloud();
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
    
    $('#lthsolr_studentpapers_filter').keyup(function() {
        listStudentPapers(0, getFacets(), $(this).val().trim(),'');
    });
    
    $('#lthsolr_projects_filter').keyup(function() {
        listProjects(0, $(this).val().trim());
    });
    
    /*$( "#searchSiteMain a" ).autocomplete({
        source: "index.php?eID=lth_solr&action=searchShort",
        minLength: 2,
        select: function( event, ui ) {
            //window.location = $('#searchsiteformlth').attr('action') + '?query=' + $( "#searchSiteMain" ).val();
            $('#searchSiteMain').val(ui.item.label.replace('<strong>','').replace('</strong>','').split(',').shift());
            $('#lthsolr_form').submit();
        }
    });*/
    
    $('#searchSiteMain').keyup(function() {
        searchLong($(this).val(), 0, 0, 0, false);
    });
    
                
    $('#lthsolr_publications_filter').keyup(function() {
        listPublications(0, getFacets(), $(this).val().trim(), $("#lthsolr_sort").val(), 0,'');
    });
    
    $('#lth_solr_tools').click(function() {
        $('#lth_solr_hidden_tools').toggle('slow');
    });
});


function mobileCheck() {
    var check = false;
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        check=true;
    }
    return check;
}


function exportStaff(syslang)
{   
    var fieldArray=[];
    $("input:checkbox[name=exportField]:checked").each(function(){
        fieldArray.push($(this).val());
    });
    
    if(fieldArray.length===0) {
        alert('You must choose one field!');
        return false;
    } 
    
    $.ajax({
        type : "POST",
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: 'exportStaff',
            tableStart: '0',
            tableLength: '1000000',
            tableFields: JSON.stringify(fieldArray),
            scope: $('#lth_solr_scope').val(),
            query: $('#lthsolr_staff_filter').val(),
            facet: getFacets(),
            syslang: syslang,
            sid: Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function(d) {
            if(d.data) {
                var csvContent = '';
                //var csvArray = [];
                var i = 0;
                $.each( d.data, function( key, aData ) {
                    for (var ii=0; ii<fieldArray.length; ii++) {
                        //csvArray[i][fieldArray[ii]] = aData[fieldArray[ii]];
                        //csvArray[i] = aData[fieldArray[ii]];
                        csvContent += aData[fieldArray[ii]] + ";";
                    }
                    csvContent += "\r\n";
                    //csvArray[i] = [aData.firstName, aData.lastName, aData.title, aData.phone, aData.email, aData.organisationName, aData.roomNumber, aData.mobile];
                    i++;
                });
                /*var csvContent='';
                csvArray.forEach(function(rowArray){
                    let row = rowArray.join(";");
                    csvContent += row + "\r\n";
                });*/
                download("data:text/csv;charset=utf-8,%EF%BB%BF" + encodeURI(csvContent), "tabledata.csv", "text/plain");
            } 
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
}


function exportPublications(syslang,tableStart,parts,t)
{
    var fieldArray=[];
    $("input:checkbox[name=exportField]:checked").each(function(){
        fieldArray.push($(this).val());
    });
    
    if(fieldArray.length===0) {
        alert('You must choose one field!');
        return false;
    } 
    
    $.ajax({
        type : "POST",
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: 'listPublications',
            scope: $('#lth_solr_scope').val(),
            query: $('#lthsolr_publications_filter').val(),
            tableStart: tableStart,
            tableLength: '2000',
            tableFields: JSON.stringify(fieldArray),
            facet: getFacets(),
            syslang: syslang,
            sid: Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function(d) {
            if(d.data) {
                //console.log(d.numFound);
                var i = 0;
                var csvContent='';
                //console.log(parts);
                if(d.numFound > 2000 && parts==0) {
                    $('.modal-body').css("float","left").after('<div style="float:left;padding:25px;0px;0px;25px;"><ul id="exportParts"></ul></div>');
                    for (var iii=0; iii<(Math.ceil(d.numFound/2000)-1); iii++) {
                        $('#exportParts').append('<li><a href="javascript:" onclick="exportPublications(\''+
                            syslang+'\','+((iii*2000))+',1,this);">Download publications ' + ((iii*2000)+1) + '-' + (iii+1)*2000 + '</a></li>');
                    }
                    if(d.numFound > (iii+1)*2000) $('#exportParts').append('<li><a href="javascript:" onclick="exportPublications(\''+
                            syslang+'\','+((iii*2000))+',1,this);">Download publications ' + ((iii*2000)+1) + '-' + d.numFound  + '</a></li>');
                } else {
                    $.each( d.data, function( key, aData ) {
                        for (var ii=0; ii<fieldArray.length; ii++) {
                            csvContent += aData[fieldArray[ii]] + ";";
                        }
                        csvContent += "\r\n";
                        /*csvContent += aData.documentTitle + ";";
                        csvContent += aData.authorName + ";";
                        csvContent += aData.publicationType + ";";
                        csvContent += aData.publicationDateYear + "-." + aData.publicationDateMonth + "-" + aData.publicationDateDay + ";";
                        csvContent += aData.pages + ";";
                        csvContent += aData.journalTitle + ";";
                        csvContent += aData.journalNumber + "\r\n";*/

                        /*if(i > 5000) {
                            i=0;
                            download("data:text/csv;charset=utf-8,%EF%BB%BF" + encodeURI(csvContent), "publications_part"+ii+".csv", "text/plain");
                            csvContent = "";
                            ii++;
                        }*/
                        i++;
                    });
                    var fileName = "publications.csv";
                    /*if(ii>1) {
                        fileName = "publications_part"+ii+".csv";
                    }*/
                    download("data:text/csv;charset=utf-8,%EF%BB%BF" + encodeURI(csvContent), fileName, "text/plain");
                    if(t) {
                        $(t).after('<i class="fa fa-check"></i>');
                    }
                }
            } 
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
}


function exportStudentPapers(syslang,tableStart,parts,t)
{
    alert('not yet');
    return false;
    var fieldArray=[];
    $("input:checkbox[name=exportField]:checked").each(function(){
        fieldArray.push($(this).val());
    });
    
    if(fieldArray.length===0) {
        alert('You must choose one field!');
        return false;
    } 
    
    $.ajax({
        type : "POST",
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: 'listPublications',
            scope: $('#lth_solr_scope').val(),
            query: $('#lthsolr_publications_filter').val(),
            tableStart: tableStart,
            tableLength: '2000',
            tableFields: JSON.stringify(fieldArray),
            facet: getFacets(),
            syslang: syslang,
            sid: Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function(d) {
            if(d.data) {
                //console.log(d.numFound);
                var i = 0;
                var csvContent='';
                //console.log(parts);
                if(d.numFound > 2000 && parts==0) {
                    $('.modal-body').css("float","left").after('<div style="float:left;padding:25px;0px;0px;25px;"><ul id="exportParts"></ul></div>');
                    for (var iii=0; iii<(Math.ceil(d.numFound/2000)-1); iii++) {
                        $('#exportParts').append('<li><a href="javascript:" onclick="exportPublications(\''+
                            syslang+'\','+((iii*2000))+',1,this);">Download publications ' + ((iii*2000)+1) + '-' + (iii+1)*2000 + '</a></li>');
                    }
                    if(d.numFound > (iii+1)*2000) $('#exportParts').append('<li><a href="javascript:" onclick="exportPublications(\''+
                            syslang+'\','+((iii*2000))+',1,this);">Download publications ' + ((iii*2000)+1) + '-' + d.numFound  + '</a></li>');
                } else {
                    $.each( d.data, function( key, aData ) {
                        for (var ii=0; ii<fieldArray.length; ii++) {
                            csvContent += aData[fieldArray[ii]] + ";";
                        }
                        csvContent += "\r\n";
                        /*csvContent += aData.documentTitle + ";";
                        csvContent += aData.authorName + ";";
                        csvContent += aData.publicationType + ";";
                        csvContent += aData.publicationDateYear + "-." + aData.publicationDateMonth + "-" + aData.publicationDateDay + ";";
                        csvContent += aData.pages + ";";
                        csvContent += aData.journalTitle + ";";
                        csvContent += aData.journalNumber + "\r\n";*/

                        /*if(i > 5000) {
                            i=0;
                            download("data:text/csv;charset=utf-8,%EF%BB%BF" + encodeURI(csvContent), "publications_part"+ii+".csv", "text/plain");
                            csvContent = "";
                            ii++;
                        }*/
                        i++;
                    });
                    var fileName = "publications.csv";
                    /*if(ii>1) {
                        fileName = "publications_part"+ii+".csv";
                    }*/
                    download("data:text/csv;charset=utf-8,%EF%BB%BF" + encodeURI(csvContent), fileName, "text/plain");
                    if(t) {
                        $(t).after('<i class="fa fa-check"></i>');
                    }
                }
            } 
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
}


function getDateAndTime()
{
    var currentdate = new Date(); 
    var datetime = currentdate.getDate() + ''
        + (currentdate.getMonth()+1) + ''
        + currentdate.getFullYear() +  ''
        + currentdate.getHours() +  ''
        + currentdate.getMinutes() + '';
        + currentdate.getSeconds() + '';
    return datetime;
}


/*function exportTableToCSV($table, filename) {

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
*/


function listStaff(tableStart, facet, query, noQuery, more)
{
    var syslang = $('html').attr('lang');
    var scope = $('#lth_solr_scope').val();
    var tableLength = $('#lth_solr_no_items').val();
    var curI;
    var inputFacet = facet;
    var exportArray = ["firstName","lastName","title","phone","email","organisationName","homepage","roomNumber","mobile"];
    //var lth_solr_staffhomepagepath = $('#lth_solr_staffhomepagepath').val();
    //var lth_solr_detailpage = $('#lth_solr_staffdetailpage').val();
    //console.log(scope);
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'listStaff',
            tableStart: tableStart,
            tableLength : tableLength,
            pid : $('#pid').val(),
            pageid : $('body').attr('id').replace('p',''),
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
                $('#lthsolr_staff_container div').remove().append('<img class="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
            }
            //$('#lthsolr_all').remove();
            $('.lthsolr_more').replaceWith('<img class="lthsolr_loader" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            var staffDetailPage = 'visa';
            if(syslang=='en') {
                staffDetailPage = 'show';
            }
                
            if(d.data) {
                var i = 0;
                var maxClass = '';
                var more = '';
                var count = '';
                var facet = '';
                var facetHeader = '';
                var content = '';
                var more = '<p class="maxlist-more"></p>';
                
                if(d.facet) {
                    $('#lth_solr_facet_container').html('');
                    if($('.item-list').length == 0 || 1+1===2) {
                        $.each( d.facet, function( key, value ) {
                            $.each( value, function( key1, value1 ) {
                                if(i > 4) {
                                    maxClass = ' class="maxlist-hidden"';
                                    more = '<p class="maxlist-more"><i class="fa fa-chevron-right"></i><a href="javascript:">' + lth_solr_messages.more + '</a></p>';
                                }

                                facet = value1[0].toString();
                                count = value1[1];
                                facetHeader = value1[2];
                                var facetCheck = '';
                                
                                if(inputFacet) {
                                    if(inArray(key + '###' + facet,JSON.parse(inputFacet))) {
                                        facetCheck = ' checked="checked"';
                                    }
                                }
                                if(parseInt(value1[1]) > 0 && value1[0]) {
                                    content += '<li' + maxClass + ' style="width:100%;">';
                                    content += facet.capitalize().replace(/_/g, ' ') + '&nbsp;[' + count + '] ';
                                    content += '<input type="checkbox" class="lth_solr_facet" name="lth_solr_facet" value="' + key + '###' + facet + '"' + facetCheck + '>';
                                    content += '</li>';
                                }
                                i++;
                            });

                            $('#lth_solr_facet_container').append('<i class="fa fa-close lthsolr_facet_close"></i><ul><li style="width:100%;"><b>'+facetHeader+'</b></li>' + content + '</ul>' + more);
                            $('.lthsolr_facet_close').click(function() {
                                $('#lth_solr_facet_container').toggle(500);
                            });
                            i=0;
                            maxClass='';
                            more='';
                            content = '';
                        });
                        createFacetClick('listStaff');
                    }
                }
            
                $.each( d.data, function( key, aData ) {
                    var template = $('#solrStaffTemplate').html();

                    var id = aData.id;
                    template = template.replace('###id###', id);

                    var displayName = aData.firstName + ' ' + aData.lastName;
                    var guid = aData.guid;
                    var uuid = aData.uuid;
                    if(!uuid) {
                        uuid=guid;
                    }

                    var homepage = window.location.href + staffDetailPage + '/' + displayName.replace(' ','-') + '('+uuid+')';
                    if(aData.homepage) {
                        homepage = aData.homepage;
                    }
                    template = template.replace(/###displayName###/g, '<a href="'+homepage+'">' + displayName + '</a>');
                    var phone = '', roomNumber = '', homepage = '';

                    if(aData.email) template = template.replace(/###email###/g, aData.email);

                    i=0;
                    //curI=0;
                    var affiliation='';
                    
                    for (i=0; i<aData.organisationId.length; i++) {
                        if(affiliation) affiliation += '<br />';
                        phone = '';
                        /*if(scope===aData.organisationId[i]) {
                            curI=i;
                        }*/
                        if(aData.title) {
                            if(aData.title[i]) affiliation += titleCase(aData.title[i]);
                        }
                        if(aData.organisationName) {
                            if(aData.organisationName[i]) affiliation += addComma(aData.organisationName[i]);
                        }

                        if(aData.phone) {
                            /*if(aData.phone[curI]) {
                                phone = aData.phone[curI];
                            } else {*/
                                if(aData.phone[i]) phone = addBreak(aData.phone[i]);
                            //}
                        }
                        if(phone) phone = phone.replace('+4646222', '+46 46 222 ').replace(/(.{2}$)/, ' $1');
                        if(aData.mobile) {
                            if(phone) phone += ', ';
                            if(aData.mobile[i]) phone += addBreak('+46 ' + aData.mobile[i].replace(/ /g, '').replace('+46','').replace(/(\d{2})(\d{3})(\d{2})(\d{2})/, "$1 $2 $3 $4"));
                        }
                        if(phone) affiliation += phone;
                    }

                    
                    template = template.replace('###affiliation###', affiliation);
                    /*template = template.replace('###title###', titleCase(title));
                    template = template.replace('###phone###', phone);
                    template = template.replace('###organisationName###', organisationName);*/

                    if(aData.homepage) {
                        homepage = '<a data-homepage="' + aData.homepage + '" href="' + aData.homepage + '"><img class="lthsolr_home" src="/typo3conf/ext/lth_solr/res/home.png" /></a>';
                    }
                    template = template.replace('###homepage###', homepage);

                    var image = '';
                    if(aData.image) image = '<img src="' + aData.image + '" />';
                    template = template.replace('###image###', image);
                    
                    template = template.replace('###lth_solr_intro###', aData.intro.replace('\n','<br />'));

                    roomNumber = aData.roomNumber;
                    if(roomNumber) {
                        roomNumber = '(' + lth_solr_messages.room + ' ' + aData.roomNumber + ')';
                    } else {
                        roomNumber = '';
                    }
                    template = template.replace('###roomNumber###', roomNumber);
                    $('#lthsolr_staff_container').append(template);
                });
                $('.lthsolr_loader').remove();
                
                $('#lthsolr_staff_header').html('<span style="">1-' + maxLength(parseInt(tableStart),parseInt(tableLength),parseInt(d.numFound)) + ' ' + lth_solr_messages.of + ' ' + d.numFound + '</span>');
                if($('#lth_solr_lu').val() === "yes" && $('.fa-download').length < 1) {
                    $('.lth_solr_filter_container').append('<i style="float:right;" class="fa fa-download fa-lg slsGray50"></i>');
                    $('.fa-download').click(function() {
                        //exportStaff(syslang);
                        if($('.modal-body .checkbox').length === 0) {
                            for (var i=0; i<exportArray.length; i++) {
                                $('.modal-body').append('<div class="checkbox"><label><input type="checkbox" name="exportField" value="'+exportArray[i]+'">'+exportArray[i]+
                                        '</label></div>');
                            }
                            $('.modal-body').append('<button id="exportButton" type="button" class="btn btn-default">Export</button>');
                            $('#exportButton').click(function(){
                                exportStaff(syslang);
                            });
                        }
                        $('#exportModal').modal('toggle');
                    });
                }
                if((parseInt(tableStart) + parseInt(tableLength)) < d.numFound) {
                    var tempMore = '<div style="margin-top:20px;" class="lthsolr_more"><button style="height:40px;" class="btn btn-default btn-lg btn-block" \n\
                        onclick="listStaff(' + (parseInt(tableStart) + parseInt(tableLength)) + ',\'\',$(\'#lthsolr_staff_filter\').val(),\'\',\'more\');">' + 
                            lth_solr_messages.show_more + ' ' + lth_solr_messages.people + 
                            ' <span class="glyphicon glyphicon-chevron-down"></span></button>';
                    /*if(d.numFound < 300) {
                        tempMore += ' | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.numFound + '); listStaff(' + (parseInt(tableStart) + parseInt(tableLength)) + ',\'\',\'\',\'\',\'more\');">' + lth_solr_messages.show_all + ' ' + d.numFound + '</a>';
                    }*/
                    tempMore += '</div>';
                    $('#lthsolr_staff_container').append(tempMore);
                }
                
                if(!mobileCheck()) {
                    $('#lthsolr_staff_container').parent().height($('#lthsolr_staff_container').height());
                    $('#lth_solr_facet_container').height($('#lthsolr_staff_container').height());
                    /*$('#lthsolr_staff_container, #lth_solr_facet_container').css('float','left');
                    $('#lthsolr_staff_container').css('width','500px');
                    $('#lth_solr_facet_container').css('width','200px');*/
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
            toggleFacets();
        }
    });
}


function maxLength(tableStart, tableLength, numFound)
{
    //console.log(tableStart + ';' + tableLength + ';' + numFound);
    if(tableStart + tableLength > numFound) {
        return numFound;
    } else {
        return parseInt(tableStart) + parseInt(tableLength);
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


function showMore()
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
}

var next;

function searchLong(term, startPeople, startPages, startCourses, more)
{
    if(term.replace('"','').length < 2) return false;
    var syslang = $('#lth_solr_syslang').val();
    var tableLength = 6;
    var webSearchScope = $('#webSearchScope').val();
    var linkStaffDetailPage = $('#linkStaffDetailPage').val();
    var template;
    
    $('.content_navigation').hide();
    $('#text_wrapper').removeClass('grid-23').removeClass('omega').addClass('grid-31');

    $('#searchsite').val(term);
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'searchLong',
            term : term,
            peopleOffset: startPeople,
            pageOffset: startPages,
            courseOffset: startCourses,
            tableLength : tableLength,
            webSearchScope: webSearchScope,
            more: more,
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: 'json',
        beforeSend: function () {
            console.log(more);
            if(startPeople == 0 && more == 0) {
                
                $('#lthsolr_staff_container').parent().show();
                console.log($('#lthsolr_staff_container').height());
                $('#lthsolr_staff_container tbody').html('').append('<tr><td class="loader"></td></tr>');
                console.log($('#lthsolr_staff_container').height());
                //$('#lthsolr_staff_container tbody').html('<img class="lthsolr_loader" id="lthsolr_loader_staff" src="/fileadmin/templates/images/ajax-loader.gif" />');
            }
            
            if((startPages == 0 && more == 0) || (startPages==0 && more=='global') || (startPages==0 && more=='local')) {
                $('#lthsolr_pages_container').parent().show();
                $('#lthsolr_pages_container').empty().append('<div class="loader"></div>');;
                //$('#lthsolr_pages_container').html('<img class="lthsolr_loader" id="lthsolr_loader_pages" src="/fileadmin/templates/images/ajax-loader.gif" />');
            }
            /*if(startDocuments > 0 || more === 'documents') {
                $('#lthsolr_documents_container').html();
                $('#lthsolr_documents_header').html('<img class="lthsolr_loader" id="lthsolr_loader_documents" src="/fileadmin/templates/images/ajax-loader.gif" />');
            }*/
           
            if(startCourses == 0 && more == 0) {
                $('#lthsolr_courses_container').parent().show();
                $('#lthsolr_courses_container tbody').html('').append('<tr><td class="loader"></td></tr>');
                //$('#lthsolr_courses_container tbody').html('');
            }
            
            //$('.lthsolr_more').replaceWith('<img class="lthsolr_loader" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            var i = 0;
            var maxClass = '';
            var moreContent = '';
            var count = '';
            var facet = '';
            var content = '';
            var id, title, teaser, url, link;

            //if(peopleOffset == 0 && pageOffset == 0 && documentOffset == 0) $('#lthsolr_staff_container').html('');
            //$('#content_navigation').append('<div class="lth_solr_left_nav"><ul class="lth_solr_res"></ul></div>');
            //console.log(more);
            /*if(d.facet) {
                $.each( d.facet, function( key, value ) {
                    //console.log(value);
                    //$.each( value, function( key1, value1 ) {
                        if(i > 5) {
                            maxClass = ' class="maxlist-hidden"';
                            moreContent = '<p class="maxlist-more"><a href="#">' + lth_solr_messages.show_all + '</a></p>';
                        }

                       // if(parseInt(value[1]) > 0) {
                            content += '<li' + maxClass + '>' + value;
                            content += '<input type="checkbox" class="lth_solr_facet" name="lth_solr_facet" value="' + value + '"></li>';
                       // }
                        i++;
                    //});

                });
                $('.lth_solr_facet_container').append('<ul><li><b>' + lth_solr_messages.staff_categories + '</b></li>' + content + '</ul>' + moreContent);
                i=0;
            }*/

            //STAFF**************************************************************************************
            var tableCounter = 3;
            var indexCounter = 1+startPeople;
            
            $('#lthsolr_staff_container .loader').parent().remove();
            var staffDetailPage = 'visa/';
            if(syslang=='en') {
                staffDetailPage = 'show/';
            }
            if(d.peopleData.length > 0) {
                //$('.lth_solr_res').append('<li>People</li>');
                $.each(d.peopleData, function( key, aData ) {
                    var uuid = aData.uuid;
                    if(!uuid) uuid = aData.guid;
                    var displayName = aData.firstName + ' ' + aData.lastName;
                    var title = aData.title[0];
                    template = $('#solrTemplate').html();
                    
                    template = template.replace('###uuid###', uuid);
                    
                    var image = '';
                    if(aData.imageId != 0 && aData.imageId != null) {
                       image = aData.imageId;
                    } else if(aData.lucrisPhoto) {
                       image = aData.lucrisPhoto;
                    }

                    //if(image !== '') image = '<div class="align_left" style="width:80px;min-height:100px;"><img style="max-height: 100%; max-width: 100%" src="' + image + '" /></div>';
                    if(image !== '') {
                        image = 'background: url(' + image + ');background-repeat:no-repeat;background-size:55px;background-position:5px 10px;padding-left:65px;z-index:1;position:relative;';
                    }
                    template = template.replace('###image###', image);

                    if(linkStaffDetailPage==="yes") {
                        template = template.replace(/###displayName###/g, indexCounter + '. <a href="'+location.protocol + '//' + location.host + location.pathname+staffDetailPage+displayName.replace(' ', '-')+'('+uuid+')">' + displayName + '</a>');
                    } else {
                        template = template.replace(/###displayName###/g, indexCounter + '. ' + displayName);
                    }
                    var title, oname = '', organisationName = '', phone = '', roomNumber = '', homePage = '', email = '';
                    
                    if(aData.organisationName) organisationName = splitString(aData.organisationName[0]+'',30);
                    if(aData.phone) {
                        phone = aData.phone[0];
                    }
                    if(phone) phone = phone.replace('+4646222', '+46 46 222 ').replace(/(.{2}$)/, ' $1');
                    if(aData.mobile) {
                        if(phone) phone += ', ';
                        phone += aData.mobile[0];
                    }
                    //if(phone) phone = $(phone).wrap('<p class="person-phone"></p>').toString();
                    if(aData.email) {
                        email = '<a href="mailto:'+aData.email+'">'+aData.email+'</a>';
                    }
                    if(!title || title == '') {
                        title = 'No title'
                    }
                    template = template.replace('###title###', addHyphen(titleCase(title)));
                    template = template.replace('###phone###', addBreak(phone));
                    template = template.replace('###email###', addBreak(email));

                    template = template.replace('###organisationName###', organisationName);

                    template = template.replace('###primaryAffiliation###', aData[7]);

                    /*if(aData[10]) {
                        homePage = lth_solr_messages.personal_homepage + ': <a data-homepage="' + aData[10] + '" href="' + aData[10] + '">' + aData[10] + '</a>';
                    } else if(aData[15]) {
                        homePage = lth_solr_messages.read_more_about + ' ' + display_name_t;
                    }
                    template = template.replace('###homepage###', '<p>' + homePage + '</p>');
                    */
                    //template = template.replace('###homepage###', '');                  
                    
                    //if(aData[12]) intro = aData[12].replace('\n','<br />');
                    //template = template.replace('###lth_solr_intro###', intro);

                    if(aData.roomNumber) {
                        roomNumber = ' (' + lth_solr_messages.room + ' ' + aData.roomNumber + ')';
                    }
                    template = template.replace('###roomNumber###', roomNumber);
                    
                    if(tableCounter === 3) {
                        $('#lthsolr_staff_container tbody').append('<tr></tr>');
                        tableCounter = 0
                    }
                    $('#lthsolr_staff_container tbody tr:last').append(template);
                    /*if(image!='') {
                        $('#'+guid).wrapInner('<div style="background-color:white;position:absolute;z-index:2;top:10px;left:68px;padding-left:5px;padding-top:5px;"></div>');
                    }*/
                    indexCounter++;
                    tableCounter++;
                });
                $('#lthsolr_people_header').html('<span class="lth_solr_search_header"><h3>' + lth_solr_messages.people + '</h3></span><span>1' + '-' + maxLength(startPeople,tableLength,d.peopleNumFound) + ' ' + lth_solr_messages.of + ' '  + d.peopleNumFound + '</span>');
                /*if((parseInt(startPeople) - parseInt(tableLength)) >= 0) {
                    $('#lthsolr_people_header').append('<span class="lthsolr_more">\n\
                    <a href="javascript:" onclick="var board_h = $(this).closest(\'.lthsolr_table_wrapper\').outerHeight();\n\
                    $(this).closest(\'.lthsolr_table_wrapper\').css(\'height\', board_h + \'px\');\n\
                    $(\'#lth_solr_no_items\').val(' + d.peopleNumFound + '); \n\
                    searchLong(\'' + term + '\',' + (parseInt(startPeople) - parseInt(tableLength)) + ',0,0,\'people\');">\n\
                    <span class="fa fa-angle-double-left"></span>' + lth_solr_messages.prev + '</a></span>');
                }*/
                if((parseInt(startPeople) + parseInt(tableLength)) < d.peopleNumFound) {
                    $('#lthsolr_staff_container tbody').append('<tr><td colspan="3" style="height:20px;">\n\
                        <button class="btn btn-default btn-lg btn-block" style="height:30px;" onclick="$(\'#lth_solr_no_items\').val(' + d.peopleNumFound + ');\n\
                        $(\'#lthsolr_staff_container tbody tr:last\').remove();\n\
                        searchLong(\'' + term + '\',' + (parseInt(startPeople) + parseInt(tableLength)) + ',0,0,\'people\');">' + lth_solr_messages.show_more + ' ' + lth_solr_messages.people +
                        ' <span class="glyphicon glyphicon-chevron-down"></span></button></td><td style="height:20px;"></td></tr>');
                }
            } else if(more==0) {
                $('#lthsolr_people_header').html('<span class="lth_solr_search_header"><h3>' + lth_solr_messages.people + '</h3></span>');
                $('#lthsolr_staff_container tbody').html('<tr><td>' + lth_solr_messages.No + ' ' + lth_solr_messages.hits + ' ' + 
                        lth_solr_messages.on + ' <b>' + term + '</b> ' + lth_solr_messages.within + ' ' + lth_solr_messages.staff + '.</td></tr>');
                //$('#lthsolr_staff_container').parent().hide();
            }
            var endI;
            
            //Pages and documents******************************************************************************************************************//
            $('#lthsolr_pages_container').find('.loader').remove();
            if(d.pageNumFound > 0) {
                var i=1;
                var indexCounter = 1+startPages;
                //Svar filter = $(d.pageData).filter('.filter-wrapper').html();
                $(d.pageData).filter('.hit').each(function( index ) {
                    template = $('#solrPagesTemplate').html();
                    id = '';
                    title = '';
                    teaser = '';
                    url = '';
                    link = '';
                    id = 'lu_'+i;
                    title = $(this).find('.title').text().trim();
                    title = title;
                    link = $(this).find('a').attr('href');
                    url = link;
                    teaser = $(this).find('.description').text().trim();
                    template = template.replace('###id###', id);
                    template = template.replace('###title###', indexCounter + '. ' + splitString(title+'',30));
                    template = template.replace('###teaser###', teaser);
                    template = template.replace('###url###', url);
                    template = template.replace('###link###', maxCharLength(link,60));
                    if(i<=5 || startPages > 0) {
                        template = template.replace('###class###', "");
                        endI = i;
                    } else {
                        template = template.replace('###class###', 'class="lth_solr_hide"');
                    }
                    $('#lthsolr_pages_container').append(template);

                    indexCounter++;
                    //console.log($(this).find('.title').text().trim());
                    i++;
                });

                var pagesHeader = '<span class="lth_solr_search_header"><h3>'+ lth_solr_messages.webpages + '</h3></span><span class="lth_solr_number">1-';
                if($('.lth_solr_hide').length > 0) {
                    pagesHeader += endI;
                } else {
                    pagesHeader += maxLength(startPages,20,d.pageNumFound);
                }
                pagesHeader += '</span> ' + lth_solr_messages.of + ' '  + d.pageNumFound;
                if(webSearchScope==='local') {
                    if(more==='global') {
                        pagesHeader += ' vid sökning inom Lunds universitet. <a href="javascript:" \n\
                        onclick="searchLong(\'' + term + '\',0,0,0,\'local\');">\n\
                        Sök inom LTH istället</a>';
                    } else {
                        pagesHeader += ' vid sökning inom LTH. <a href="javascript:" \n\
                        onclick="searchLong(\'' + term + '\',0,0,0,\'global\');">\n\
                        Sök inom hela Lunds universitet istället</a>';
                    }
                    
                } 
                if(webSearchScope==='global') {
                    pagesHeader += ' vid sökning inom Lunds universitet.';
                }
                pagesHeader += '</span>';
                $('#lthsolr_pages_header').html(pagesHeader);
                

                /*if((parseInt(startPages) - parseInt(tableLength)) >= 0) {
                    $('#lthsolr_pages_header').append('<span class="lthsolr_more">\n\
                    <a href="javascript:" onclick= "var board_h = $(this).closest(\'.lthsolr_table_wrapper\').outerHeight();\n\
                    $(this).closest(\'.lthsolr_table_wrapper\').css(\'height\', board_h + \'px\');\n\
                    $(\'#lth_solr_no_items\').val(' + d.pageNumFound + '); \n\
                    searchLong(\'' + term + '\',0,' + (parseInt(startPages) - parseInt(tableLength)) + ',0,0,\'pages\');">\n\
                    <span class="fa fa-angle-double-left"></span>' + lth_solr_messages.prev + '</a></span>');
                }*/
                if((parseInt(startPages) + parseInt(20)) < d.pageNumFound) {
                    next = '<li>\n\
                        <button class="btn btn-default btn-lg btn-block" style="height:30px;" href="javascript:" \n\
                        onclick="$(\'#lth_solr_no_items\').val(' + d.pageNumFound + '); \n\
                        $(\'#lthsolr_pages_container li:last\').remove();\n\
                        searchLong(\'' + term + '\',0,' + (parseInt(startPages) + parseInt(20)) + ',0,\''+ webSearchScope + '\');">\n\
                        ' + lth_solr_messages.show_more + ' ' + lth_solr_messages.webpages + ' <span class="glyphicon glyphicon-chevron-down"></span></button></li>';
                }
                if($('.lth_solr_hide').length > 0) {
                    $('#lthsolr_pages_container').append('<li>\n\
                        <button class="btn btn-default btn-lg btn-block" style="height:30px;" href="javascript:" onclick="$(\'.lth_solr_hide\').show(300);\n\
                        $(\'li\').removeClass(\'lth_solr_hide\');\n\
                        $(\'.lth_solr_number\').text(\'1-20\');\n\
                        $(\'#lthsolr_pages_container li:last\').replaceWith(next);">\n\
                        ' + lth_solr_messages.show_more + ' ' + lth_solr_messages.webpages + ' <span class="glyphicon glyphicon-chevron-down"></span></button></li>');
                } else if((parseInt(startPages) + parseInt(20)) < d.pageNumFound) {
                    $('#lthsolr_pages_container').append(next);
                }
            } else if(more==0) {
                $('#lthsolr_pages_header').html('<span class="lth_solr_search_header"><h3>'+ lth_solr_messages.webpages + '</h3></span>');
                $('#lthsolr_pages_container tbody').html('<div>' + lth_solr_messages.No + ' ' + lth_solr_messages.hits + ' ' + 
                        lth_solr_messages.on + ' <b>' + term + '</b> ' + lth_solr_messages.within + ' ' + lth_solr_messages.pages + '.</div>');
                //$('#lthsolr_pages_container').parent().hide();
            }
            
            /*var tableCounter = 4;
            var indexCounter = 1+startPages;
            if(d.pageData.length > 0) {
                
                //$('.lth_solr_res').append('<li>Pages</li>');
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

                    template = template.replace('###id###', id);
                    template = template.replace('###title###', indexCounter + '. ' + splitString(title+'',30));
                    template = template.replace('###teaser###', teaser);
                    template = template.replace('###url###', url);
                    template = template.replace('###link###', link);
                    
                    $('#lthsolr_pages_container').append(template);
                    
                    indexCounter++;
                });
                
                $('#lthsolr_loader_pages').remove();

                $('#lthsolr_pages_header').html('<span class="lth_solr_search_header">'+ lth_solr_messages.webpages + '</span><span>' + (parseInt(startPages) + 1) + '-' + maxLength(startPages,tableLength,d.pageNumFound) + ' ' + lth_solr_messages.of + ' '  + d.pageNumFound + '</span>');

                if((parseInt(startPages) - parseInt(tableLength)) >= 0) {
                    $('#lthsolr_pages_header').append('<span class="lthsolr_more">\n\
                    <a href="javascript:" onclick= "var board_h = $(this).closest(\'.lthsolr_table_wrapper\').outerHeight();\n\
                    $(this).closest(\'.lthsolr_table_wrapper\').css(\'height\', board_h + \'px\');\n\
                    $(\'#lth_solr_no_items\').val(' + d.pageNumFound + '); \n\
                    searchLong(\'' + term + '\',0,' + (parseInt(startPages) - parseInt(tableLength)) + ',0,0,\'pages\');">\n\
                    <span class="fa fa-angle-double-left"></span>' + lth_solr_messages.prev + '</a></span>');
                }
                if((parseInt(startPages) + parseInt(tableLength)) < d.pageNumFound) {
                   $('#lthsolr_pages_header').append('<span class="lthsolr_more">\n\
                        <a href="javascript:" onclick="var board_h = $(this).closest(\'.lthsolr_table_wrapper\').outerHeight();\n\
                        $(this).closest(\'.lthsolr_table_wrapper\').css(\'height\', board_h + \'px\');\n\
                        $(\'#lth_solr_no_items\').val(' + d.pageNumFound + '); \n\
                        searchLong(\'' + term + '\',0,' + (parseInt(startPages) + parseInt(tableLength)) + ',0,0,\'pages\');">\n\
                        <span class="fa fa-angle-double-right"></span>' + lth_solr_messages.next + '</a></span>');
                }
            } else if(more=='') {
                console.log('756'+more);
                $('#lthsolr_pages_container').parent().remove();
            }*/
            
            /*var tableCounter = 4;
            var indexCounter = 1;
            if(d.documentData.length > 0) {
                $('.lth_solr_res').append('<li>Documents</li>');
                $.each( d.documentData, function( key, aData ) {
                    template = $('#solrDocumentsTemplate').html();
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

                $('#lthsolr_documents_header').html('<span class="lth_solr_search_header">'+ lth_solr_messages.documents + '</span><span>' + (parseInt(startPeople) + 1) + '-' + maxLength(startDocuments,tableLength,d.documentNumFound) + ' ' + lth_solr_messages.of + ' '  + d.documentNumFound + '</span>');
                if((parseInt(startDocuments) - parseInt(tableLength)) >= 0) {
                    $('#lthsolr_documents_header').append('<span class="lthsolr_more">\n\
                    <a href="javascript:" onclick="var board_h = $(this).closest(\'.lthsolr_table_wrapper\').outerHeight(); \n\
                    $(this).closest(\'.lthsolr_table_wrapper\').css(\'height\', board_h + \'px\');\n\
                    $(\'#lth_solr_no_items\').val(' + d.documentNumFound + '); \n\
                    searchLong(\'' + term + '\',0,0,' + (parseInt(startDocuments) - parseInt(tableLength)) + ',0,\'documents\',0);">\n\
                    <span class="fa fa-angle-double-left"></span>' + lth_solr_messages.prev + '</a></span>');
                }
                if((parseInt(startDocuments) + parseInt(tableLength)) < d.documentNumFound) {
                    $('#lthsolr_documents_header').append('<span class="lthsolr_more">\n\
                        <a href="javascript:" onclick="var board_h = $(this).closest(\'.lthsolr_table_wrapper\').outerHeight();\n\
                        $(this).closest(\'.lthsolr_table_wrapper\').css(\'height\', board_h + \'px\');\n\
                        $(\'#lth_solr_no_items\').val(' + d.documentsNumFound + '); \
                        searchLong(\'' + term + '\',0,0,' + (parseInt(startDocuments) + parseInt(tableLength)) + ',0,\'documents\');">' + lth_solr_messages.next + 
                        '<span class="fa fa-angle-double-right"></span></a></span>');
                }
            } else if(more=='') {
                //$('#lthsolr_loader_documents').remove();
                $('#lthsolr_documents_container').parent().remove();
            }*/
                
            // COURSES**************************************************************************************************************//
            var tableCounter = 3;
            var indexCounter = 1+startCourses;
            $('#lthsolr_courses_container tbody tr:first').remove();
            var courseCode, credit, homepage;

            if(d.courseData.length > 0) {
                $('.lth_solr_res').append('<li>Courses</li>');
                $.each( d.courseData, function( key, aData ) {
                    template = $('#solrCoursesTemplate').html();
                    id = '';
                    title = '';
                    courseCode = '';
                    homepage = '';
                    link = '';
                    credit = '';

                    if(aData.id) id = aData.id;
                    if(aData.title) title = aData.title;
                    if(aData.courseCode) courseCode = aData.courseCode;
                    if(aData.credit) credit = aData.credit + 'hp';
                    if(aData.homepage) {
                        homepage = aData.homepage.toString();
                        if(homepage.substr(0,4) != 'http') {
                            homepage = 'http://' + homepage;
                        }
                        link = '<p><a href="' + homepage + '">' + maxCharLength(homepage,25) + '</a><p>';
                    }

                    template = template.replace('###id###', id);
                    template = template.replace('###title###', indexCounter + '. ' + splitString(title+'',30));
                    template = template.replace('###course_code###', courseCode);
                    template = template.replace('###link###', link);
                    template = template.replace('###credit###', credit);
                    
                    //$('#lthsolr_documents_container').append(template);
                    if(tableCounter === 3) {
                        $('#lthsolr_courses_container tbody').append('<tr></tr>');
                        tableCounter = 0
                    }
                    $('#lthsolr_courses_container tbody tr:last').append(template);
                    tableCounter++;
                    indexCounter++;
                });

                //$('#lthsolr_loader_courses').remove();
                $('#lthsolr_courses_header').html('<span class="lth_solr_search_header"><h3>'+ lth_solr_messages.courses + '</h3></span><span>1-' + maxLength(startCourses,tableLength,d.courseNumFound) + ' ' + lth_solr_messages.of + ' '  + d.courseNumFound + '</span>');
                
                /*if((parseInt(startCourses) - parseInt(tableLength)) >= 0) {
                    $('#lthsolr_courses_header').append('<span class="lthsolr_more">\n\
                        <a href="javascript:" onclick="var board_h = $(this).closest(\'.lthsolr_table_wrapper\').height(); \n\
                        $(this).closest(\'.lthsolr_table_wrapper\').css(\'outerHeight\', board_h + \'px\');\n\
                        $(\'#lth_solr_no_items\').val(' + d.documentNumFound + '); \n\
                        searchLong(\'' + term + '\',0,0,' + (parseInt(startCourses) - parseInt(tableLength)) + ',\'courses\');">\n\
                        <span class="fa fa-angle-double-left"></span>' + lth_solr_messages.prev + '</a></span>');
  
                }*/
                if((parseInt(startCourses) + parseInt(tableLength)) < d.courseNumFound) {
                    $('#lthsolr_courses_container tbody').append('<tr><td colspan="3" style="height:20px;">\n\
                        <button class="btn btn-default btn-lg btn-block" style="height:30px;" \n\
                        onclick="var board_h = $(this).closest(\'.lthsolr_table_wrapper\').height();\n\
                        $(\'#lth_solr_no_items\').val(' + d.courseNumFound + '); \n\
                        $(\'#lthsolr_courses_container tbody tr:last\').remove();\n\
                        searchLong(\'' + term + '\',0,0,' + (parseInt(startCourses) + parseInt(tableLength)) + ',\'courses\');">' + 
                        lth_solr_messages.show_more + ' ' + lth_solr_messages.courses +
                        ' <span class="glyphicon glyphicon-chevron-down"></span></button></td><td style="height:20px;"></td></tr>');
                }
            } else if(more==0) {
                $('#lthsolr_courses_header').html('<span class="lth_solr_search_header"><h3>'+ lth_solr_messages.courses + '</h3></span>');
                $('#lthsolr_courses_container tbody').html('tr><td>' + lth_solr_messages.No + ' ' + lth_solr_messages.hits + 
                        ' ' + lth_solr_messages.on + ' <b>' + term + '</b> ' + lth_solr_messages.within + ' ' + lth_solr_messages.courses + '.</td></tr>');
                //$('#lthsolr_pages_container').parent().hide();
            }
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
}


function maxCharLength(input,noChar)
{
    if(input) {
        if(input.length > noChar) {
            input = input.substr(0,noChar) + '...';
        }
    }
    return input;
}

function addBreak(input)
{
    if(input && input !='') {
        input = '<br />' + input.toString().replace(',',', ');
    }
    return input;
}

function addComma(input)
{
    if(input) {
        input = ', ' + input;
    }
    return input;
}

function addHyphen(input)
{
    if(input) {
        input = ' - ' + input.toString().replace(',',', ');
    }
    return input;
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


function listPublications(tableStart, facet, query, sorting, more, lastGroupValue)
{
    var syslang = $('html').attr('lang');
    var scope = $('#lth_solr_scope').val();
    var tableLength = $('#lth_solr_no_items').val();
    var keyword = $('#lth_solr_keyword').val();
    var pageTitle = $('#lth_solr_pagetitle').val();
    var publicationCategories = $('#lth_solr_publicationCategories').val();
    var publicationCategoriesSwitch = $('#lth_solr_publicationCategoriesSwitch').val();
    var inputFacet = facet;
    var i = 0;
    var maxClass, count, facetHeader, more, content, numberOfPages, publicationDate, journalTitle, title, placeOfPublication, authorName, documentTitle;
    var id, publisher, attachmentLimitedVisibility, attachmentMimeType, attachmentTitle, attachmentSize, attachmentUrl, attachment, hostPublicationTitle;
    var volume, pages, articleNumber;
    var exportArray = ["articleNumber","authorName","documentTitle","documentLimitedVisibility","documentMimeType","documentSize",
            "documentUrl","hostPublicationTitle","id","journalTitle","journalNumber","numberOfPages","pages","publicationType","publicationDateYear",
            "publicationDateMonth","publicationDateDay","placeOfPublication","publisher","volume"];
        
    if(publicationCategoriesSwitch === 'all') {
        publicationCategories = '';
    } else if(publicationCategoriesSwitch === 'FREE' || publicationCategoriesSwitch === 'CAMPUS') {
        publicationCategories = publicationCategoriesSwitch;
    }

    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: 'listPublications',
            tableStart: tableStart,
            tableLength: tableLength,
            pid: $('#pid').val(),
            pageid: $('body').attr('id'),
            scope: scope,
            syslang: syslang,
            query: query,
            addPeople: $('#addPeople').val(),
            selection: $('#lth_solr_selection').val(),
            facet: facet,
            keyword: keyword,
            sorting: sorting,
            publicationCategories: publicationCategories,
            sid: Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            if(!more) {
                //console.log('1080');
                $('.lthsolr_publication_row').remove();
                $('#lthsolr_publications_container').before('<div class="loader"></div>');
            } else {
                $('.lthsolr_more').html('').addClass('loader');
            }
        },
        success: function(d) {
            $('.loader').remove();
            $('.lthsolr_more').remove();
            if(pageTitle) {
                pageTitle = ' ' + titleCase(pageTitle) + ' ';
            } else {
                pageTitle='';
            }
            $('#lthsolr_publications_header').html('' + lth_solr_messages.publications + pageTitle + '');
            if(d.data) {
                
                if(d.facet) {
                    $('#lth_solr_facet_container').html('');
                    if($('.item-list').length == 0 || 1+1===2) {
                        $.each( d.facet, function( key, value ) {
                            maxClass='';
                            more='';
                            content = '';
                            $.each( value, function( key1, value1 ) {
                                if(i > 4) {
                                    maxClass = ' class="maxlist-hidden"';
                                    more = '<p class="maxlist-more"><i class="fa fa-chevron-right"></i><a href="#">' + lth_solr_messages.more + '</a></p>';
                                }

                                facet = value1[0].toString();
                                count = value1[1];
                                facetHeader = value1[2];
                                var facetCheck = '';
                                
                                if(inputFacet) {
                                    if(inArray(key + '###' + facet,JSON.parse(inputFacet))) {
                                        facetCheck = ' checked="checked"';
                                    }
                                }
                                if(parseInt(value1[1]) > 0 && value1[0]) {
                                    content += '<li' + maxClass + ' style="width:100%;">';
                                    content += facet.capitalize().replace(/_/g, ' ') + '&nbsp;[' + count + '] ';
                                    content += '<input type="checkbox" class="lth_solr_facet" name="lth_solr_facet" value="' + key + '###' + facet + '"' + facetCheck + '>';
                                    content += '</li>';
                                }
                                i++;
                            });

                            $('#lth_solr_facet_container').append('<i class="fa fa-close lthsolr_facet_close"></i><ul><li style="width:100%;"><b>'+facetHeader+'</b></li>' + content + '</ul>' + more);
                            $('.lthsolr_facet_close').click(function() {
                                $('#lth_solr_facet_container').toggle(500);
                            });
                            i=0;
                        });
                        createFacetClick('listPublications', sorting);
                    }
                }

                var publicationDetailPage = 'visa';
                if(syslang=='en') {
                    publicationDetailPage = 'show';
                }
                
                $.each( d.data, function( key, aData ) {
                    if(sorting==='publicationYear') {
                        if(lastGroupValue!=aData.publicationDateYear) {
                            $('#lthsolr_publications_container').append('<div class="lthsolr_publication_row" style="margin-top:0px;">'+aData.publicationDateYear+'</div>');
                        }
                    }
                    if(sorting==='publicationType') {
                        if(lastGroupValue!=aData.publicationType) {
                            $('#lthsolr_publications_container').append('<div class="lthsolr_publication_row" style="margin-top:0px;">'+aData.publicationType+'</div>');
                        }
                    }
                    var template = $('#solrPublicationTemplate').html();
                    
                    articleNumber = '';
                    attachment = '';
                    attachmentLimitedVisibility = '';
                    attachmentSize = '';
                    attachmentMimeType = '';
                    attachmentUrl = '';
                    attachmentTitle = '';
                    authorName = '';
                    documentTitle = '';
                    hostPublicationTitle = '';
                    journalTitle = '';
                    numberOfPages = '';
                    pages = '';
                    publicationDate = '';
                    publisher = '';
                    placeOfPublication = '';
                    volume = '';
                    
                    //id
                    id = aData.id;
                    
                    //documentTitle
                    if(aData.documentTitle) {
                        title = aData.documentTitle.charAt(0).toUpperCase() + aData.documentTitle.slice(1).toLowerCase();
                    } else {
                        title = 'untitled';
                    }
                    
                    var path = '';
                    if(window.location.href.indexOf('(') > 0) {
                        path = window.location.href.split('(').shift().split('/');
                        path.pop();
                        path = path.join('/');
                    } else if(window.location.href.indexOf('?') > 0) {
                        path = window.location.href.split('?').shift().split('/');
                        path.pop();
                        path = path.join('/');
                    } else {
                        path = window.location.href + publicationDetailPage;
                    }

                    title = '<a href="' + path + '/' + title.replace(/[^\w\s-]/g,'').replace(/ /g,'-').toLowerCase() + '('+id+')(publication)">' + title + '</a>';
                    
                    //articleNumber
                    if(aData.articleNumber) articleNumber = ', ' + aData.articleNumber;
                    
                    //attachmentLimitedVisibility
                    if(aData.attachmentLimitedVisibility) attachmentLimitedVisibility = aData.attachmentLimitedVisibility;
                            
                    //attachmentMimeType
                    if(aData.attachmentMimeType) attachmentMimeType = aData.attachmentMimeType;
                            
                    //attachmentSize
                    if(aData.attachmentSize) attachmentSize = aData.attachmentSize;
                    
                    //attachmentUrl
                    if(aData.attachmentUrl) attachmentUrl = aData.attachmentUrl;
        
                    //authorName
                    if(aData.authorName) authorName = aData.authorName + '. ';
                    
                    //hostPublicationTitle
                    if(aData.hostPublicationTitle) hostPublicationTitle = '<i>' + aData.hostPublicationTitle + '</i>. ';
                    
                    //pages
                    if(aData.pages) pages = lth_solr_messages.pagesAbbreviation + ' ' + aData.pages + ' ';
                    
                    //publicationDate
                    if(aData.publicationDateYear) publicationDate = aData.publicationDateYear;
                    if(aData.publicationDateMonth) publicationDate += '-'+aData.publicationDateMonth;
                    if(aData.publicationDateDay) publicationDate += '-'+aData.publicationDateDay;
                    if(publicationDate) publicationDate = publicationDate + ' ';
                    
                    //publisher
                    if(aData.publisher) {
                        publisher = aData.publisher + ' ';
                    }
                    //placeOfPublication
                    if(aData.placeOfPublication) {
                        placeOfPublication = aData.placeOfPublication + ': ';
                    }
                    //numberOfPages
                    if(aData.numberOfPages) {
                        numberOfPages = aData.numberOfPages + ' ' + lth_solr_messages.pagesAbbreviation;
                    }
                    if(aData.journalTitle) {
                        journalTitle = ' ' + lth_solr_messages.in + ': ' + aData.journalTitle + '.';
                    }
                    if(aData.journalTitle && aData.journalNumber) journalTitle += ' ' + aData.journalNumber + ', ';
                    
                    //volume
                    if(aData.volume) {
                        volume = aData.volume + ',';
                    }

                    template = template.replace('###articleNumber###', articleNumber);
                    template = template.replace('###authorName###', authorName);
                    template = template.replace('###id###', id);
                    template = template.replace('###hostPublicationTitle###', hostPublicationTitle);
                    template = template.replace('###journalTitle###', journalTitle);
                    template = template.replace('###numberOfPages###', numberOfPages);
                    template = template.replace('###pages###', pages);
                    template = template.replace('###publicationType###', aData.publicationType);
                    template = template.replace('###publicationDate###', publicationDate);
                    template = template.replace('###publisher###', publisher);
                    template = template.replace('###placeOfPublication###', placeOfPublication);
                    template = template.replace('###title###', title);
                    template = template.replace('###volume###', volume);

                    $('#lthsolr_publications_container').append(template);

                    if(attachmentLimitedVisibility || attachmentUrl) {
                        if(attachmentLimitedVisibility==='FREE') {
                            attachment = '<i class="fa fa-unlock"></i>';
                        } else if(attachmentLimitedVisibility==='CAMPUS') {
                            attachment = '<i class="fa fa-lock"></i>';
                        } 
                        if(attachmentUrl) {
                            attachment += '<i class="fa fa-paperclip"></i>';
                        }
                        $('#'+id).append('<div class="lthsolr_attachments">'+attachment+'</div>');
                    }
                    if(sorting==='publicationYear') {
                        lastGroupValue = aData.publicationDateYear;
                    }
                    if(sorting==='publicationType') {
                        lastGroupValue = aData.publicationType;
                    }
                });
                
                $('.lthsolr_loader').remove();
                var sortButton = '<select id="lthsolr_sort" style="direction: rtl;font-size:17px;"></select>';
                $('#lthsolr_publications_sort').html(sortButton);
                var sortoptions = ["publicationYear,Publikationsår,&#xf161;","publicationType,typ,&#xf160;","documentTitle,Titel,&#xf160;","authorName,Författarens efternamn,&#xf160;"];
                $(sortoptions).each(function(index, value){
                    var option = '<option value="' + value.toString().split(',')[0] + '"';
                    if(sorting == value.toString().split(',')[0]) {
                        option += ' selected="selected"';
                    }
                    option += '>' + value.toString().split(',')[1] + ' ' + value.toString().split(',')[2] + '</option>';
                    $('#lthsolr_sort').append(option);
                });
                $('#lthsolr_publications_header').append(' (1-' + maxLength(parseInt(tableStart),parseInt(tableLength),parseInt(d.numFound)) + ' ' + lth_solr_messages.of + ' ' + d.numFound + ')');
                if($('#lth_solr_lu').val() === "yes" && $('.fa-download').length < 1) {
                    $('.lth_solr_filter_container').append('<i style="float:right;margin-top:12px;" class="fa fa-download fa-lg slsGray50"></i>');
                    $('.fa-download').click(function() {
                        //exportPublications(syslang);
                        if($('.modal-body .checkbox').length === 0) {
                            for (var i=0; i<exportArray.length; i++) {
                                $('.modal-body').append('<div class="checkbox"><label><input type="checkbox" name="exportField" value="'+exportArray[i]+'">'+exportArray[i]+
                                        '</label></div>');
                            }
                            $('.modal-body').prepend('<div class="checkbox"><label><input type="checkbox" id="select_all" /></label><i class="fa fa-check"></i></div>');
                            $('.modal-body').append('<button id="exportButton" type="button" class="btn btn-default">Export</button>');
                            $('.modal-body').wrap('<form></form>');
                            $('#exportButton').click(function(){
                                exportPublications(syslang,0,0,null);
                            });
                            $('#select_all').change(function() {
                                var checkboxes = $(this).closest('form').find(':checkbox');
                                checkboxes.prop('checked', $(this).is(':checked'));
                            });
                        }
                        $('#exportModal').modal('toggle');
                    });
                }
                if((parseInt(tableStart) + parseInt(tableLength)) < d.numFound) {
                    var tempMore = '<div style="margin-top:20px;" class="lthsolr_more"><button style="height:40px;" class="btn btn-default btn-lg btn-block"\n\
                     onclick="listPublications(' + (parseInt(tableStart) + parseInt(tableLength)) + ',\'\',\'\',\'' + sorting + '\',\'more\',\''+
                            lastGroupValue+'\');">' + lth_solr_messages.show_more + ' ' + lth_solr_messages.publications + 
                            ' <span class="glyphicon glyphicon-chevron-down"></span></button>';

                    tempMore += '</div>';
                    $('#lthsolr_publications_container').append(tempMore);
                }
                if(!mobileCheck()) {
                    $('#lthsolr_publications_container').parent().height($('#lthsolr_publications_container').height());
                    $('#lth_solr_facet_container').height($('#lthsolr_publications_container').height());
                    $('#lthsolr_publications_container, #lth_solr_facet_container').css('float','left');
                }
            } else if(!query) {
                $('.lth_solr_filter_container').next().remove();
                $('.lth_solr_filter_container').remove();
            }
            
            /*$('.lthsolr_publication_row').on( 'click', function () {
                if(lth_solr_detailpage) {
                    var id = $(this).attr('id');
                    //console.log(id);
                    window.location.href = lth_solr_detailpage + '?no_cache=1&uuid=' + id;
                }
            });*/
            
            $("#lthsolr_sort").change(function(){
                listPublications(0,inputFacet,query,$( this ).val(),0,'');
            });
            
            toggleFacets();
        }
    });
}
 

function listTagCloud()
{
    var syslang = $('html').attr('lang');
    var scope = $('#lth_solr_scope').val();
    var publicationDetailPage = 'publikationer';
    if(syslang=='en') {
        publicationDetailPage = 'publications';
    }
    path = window.location.pathname + publicationDetailPage;
    
    $.ajax({
        type : "POST",
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'listTagCloud',
            scope : scope,
            syslang : syslang,
            term : encodeURIComponent(path),
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: "json",
        beforeSend: function () {
            $('#lthsolr_tagcloud_container').html('<img class="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            if(d) {
                $('#lthsolr_tagcloud_container').html('');
                $('#lthsolr_tagcloud_container').jQCloud(d.data);
                
            }
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
}


function listStudentPapers(tableStart, facet, query, more)
{
    var syslang = $('html').attr('lang');
    var scope = $('#lth_solr_scope').val();
    var tableLength = $('#lth_solr_no_items').val();
    var detailPage = $('#lth_solr_detailpage').val();
    var inputFacet = facet;
    var maxClass = '';
    var count = '';
    var content = '';
    var exportArray = ["documentTitle","authors","organisations","externalOrganisations","publicationType","language","publicationDateYear","keywords",
            "documentUrl","supervisorName","organisationSourceId"];
    var i = 0;
    var maxClass, more, title, facetHeader;
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'listStudentPapers',
            tableStart: tableStart,
            tableLength : tableLength,
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
            alert(jq + ';' + st + " : " + err);
        },
        beforeSend: function () {
            if(!more) {
                $('.lthsolr_publication_row').remove();
                $('#lthsolr_publications_container').before('<div class="loader"></div>');
            } else {
                $('.lthsolr_more').html('').addClass('loader');
            }
        },
        success: function(d) {
            $('.loader').remove();
            $('.lthsolr_more').remove();
            if(d.data) {
                if(d.facet) {
                    $('#lth_solr_facet_container').html('');
                    $.each( d.facet, function( key, value ) {

                        $.each( value, function( key1, value1 ) {
                            if(i > 4) {
                                maxClass = ' class="maxlist-hidden"';
                                more = '<p class="maxlist-more"><span class="glyphicon glyphicon-menu-right" aria-hidden="true"></span><a href="#">' + lth_solr_messages.more + '</a></p>';
                            }

                            facet = value1[0].toString();
                            count = value1[1];
                            facetHeader = value1[2];
                            var facetCheck = '';

                            if(inputFacet) {
                                if(inArray(key + '###' + facet,JSON.parse(inputFacet))) {
                                    facetCheck = ' checked="checked"';
                                }
                            }
                            if(parseInt(value1[1]) > 0 && value1[0]) {
                                content += '<li' + maxClass + ' style="width:100%;">';
                                content += facet.capitalize().replace(/_/g, ' ') + '&nbsp;[' + count + '] ';
                                content += '<input type="checkbox" class="lth_solr_facet" name="lth_solr_facet" value="' + key + '###' + facet + '"' + facetCheck + '>';
                                content += '</li>';
                            }
                            i++;
                        });

                        $('#lth_solr_facet_container').append('<i class="fa fa-close lthsolr_facet_close"></i><ul><li style="width:100%;"><b>'+facetHeader+'</b></li>' + content + '</ul>' + more);
                        $('.lthsolr_facet_close').click(function() {
                            $('#lth_solr_facet_container').toggle(500);
                        });
                        i=0;
                        maxClass='';
                        more='';
                        content = '';
                    });
                    createFacetClick('listStudentPapers');
                }
                
                var publicationDetailPage = 'visa';
                if(syslang=='en') {
                    publicationDetailPage = 'show';
                }
                var path = window.location.href + publicationDetailPage;
                
                $.each( d.data, function( key, aData ) {
                    var template = $('#solrPublicationTemplate').html();

                    if(aData[1]) {
                        //title = '<a href="index.php?id=' + detailPage + '&uuid=' + aData[0] + '&no_cache=1">' + aData[1] + '</a>';
                        title = aData[1].charAt(0).toUpperCase() + aData[1].slice(1).toLowerCase();
                    } else {
                        title = 'untitled';
                    }
                    
                    title = '<a href="' + path + '/' + title.replace(/[^\w\s-]/g,'').replace(/ /g,'-').toLowerCase() + '('+aData[0]+')">' + title + '</a>';

                    template = template.replace('###id###', aData[0]);
                    template = template.replace('###docTitle###', title);
                    template = template.replace('###authorName###', aData[2]);
                    template = template.replace(/###publicationDateYear###/g, aData[3]);
                    template = template.replace('###organisationName###', aData[4]);

                    $('#lthsolr_publications_container').append(template);
                });
                
                $('.lthsolr_loader').remove();

                $('#lthsolr_publications_header').html('<div style="float:left;">1-' + maxLength(parseInt(tableStart),parseInt(tableLength),parseInt(d.numFound)) + ' ' + lth_solr_messages.of + ' ' + d.numFound + '</div><div style="float:right;"></div>');
                if($('#lth_solr_lu').val() === "nja" && $('.fa-download').length < 1) {
                    $('.lth_solr_filter_container').append('<i style="float:right;margin-top:12px;" class="fa fa-download fa-lg slsGray50"></i>');
                    $('.fa-download').click(function() {
                        //exportPublications(syslang);
                        if($('.modal-body .checkbox').length === 0) {
                            for (var i=0; i<exportArray.length; i++) {
                                $('.modal-body').append('<div class="checkbox"><label><input type="checkbox" name="exportField" value="'+exportArray[i]+'">'+exportArray[i]+
                                        '</label></div>');
                            }
                            $('.modal-body').prepend('<div class="checkbox"><label><input type="checkbox" id="select_all" /></label><i class="fa fa-check"></i></div>');
                            $('.modal-body').append('<button id="exportButton" type="button" class="btn btn-default">Export</button>');
                            $('.modal-body').wrap('<form></form>');
                            $('#exportButton').click(function(){
                                exportStudentPapers(syslang,0,0,null);
                            });
                            $('#select_all').change(function() {
                                var checkboxes = $(this).closest('form').find(':checkbox');
                                checkboxes.prop('checked', $(this).is(':checked'));
                            });
                        }
                        $('#exportModal').modal('toggle');
                    });
                }
                
                /*if((parseInt(tableStart) + parseInt(tableLength)) < d.numFound) {
                    $('#lthsolr_publications_container').append('<div style="margin-top:20px;" class="lthsolr_more"><a href="javascript:" onclick="listStudentPapers(' + (parseInt(tableStart) + parseInt(tableLength)) + ');">' + lth_solr_messages.next + ' ' + tableLength + ' ' + lth_solr_messages.of + ' ' + d.numFound + '</a> | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.numFound + '); listStudentPapers(' + (parseInt(tableStart) + parseInt(tableLength)) + ');">' + lth_solr_messages.show_all + ' ' + d.numFound + '</a></div>');
                }*/
                if((parseInt(tableStart) + parseInt(tableLength)) < d.numFound) {
                    var tempMore = '<div style="margin-top:20px;" class="lthsolr_more"><button style="height:40px;" class="btn btn-default btn-lg btn-block"\n\
                     onclick="listStudentPapers(' + (parseInt(tableStart) + parseInt(tableLength)) + ',\'\',\'\',\'more\');">' + lth_solr_messages.show_more + ' ' + lth_solr_messages.publications + 
                            ' <span class="glyphicon glyphicon-chevron-down"></span></button>';

                    tempMore += '</div>';
                    $('#lthsolr_publications_container').append(tempMore);
                }
                if(!mobileCheck()) {
                    $('#lthsolr_publications_container').parent().height($('#lthsolr_publications_container').height());
                    $('#lth_solr_facet_container').height($('#lthsolr_publications_container').height());
                    $('#lthsolr_publications_container, #lth_solr_facet_container').css('float','left');
                }
            }
            toggleFacets();
        }
    });
}


function showStudentPaper()
{
    var syslang = $('html').attr('lang');
    var abstract,documentTitle,authors,organisations,externalOrganisations,publicationType,language,publicationDateYear,
        keywords,documentUrl,supervisorName,organisationSourceId,bibtex;
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'showStudentPaper',
            term : $('#lth_solr_uuid').val(),
            syslang : syslang,
            //detailPage: lth_solr_staffdetailpage + ',' + lth_solr_projectdetailpage,
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: 'json',
        beforeSend: function () {
            $('#lth_solr_container').html('<img src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            //console.log(d);
            abstract = d.data.abstract;
            documentTitle = d.data.documentTitle;
            authors = d.data.authors;
            organisations = d.data.organisations;
            externalOrganisations = d.data.externalOrganisations;
            publicationType = d.data.publicationType;
            language = d.data.language;
            publicationDateYear = d.data.publicationDateYear;
            keywords = d.data.keywords;
            documentUrl = d.data.documentUrl;
            supervisorName = d.data.supervisorName;
            organisationSourceId = d.data.organisationSourceId;
            bibtex = d.data.bibtex;
            
            var organisations = '';
            var path = window.location.href.split('(').shift().split('/');
            path.pop();
            path = path.join('/');
                
            if(d.data) {
                if(organisationSourceId) {
                   organisations = '<a href="' + path + '/' + organisations + '('+ organisationSourceId + ')">' + organisations + '</a>';
                } 
                
                var template = $('#solrTemplate').html();
                template = template.replace('###abstract###', checkData(abstract, lth_solr_messages.abstract));
                template = template.replace('###title###', documentTitle);
                template = template.replace('###authors###', checkData(authors, lth_solr_messages.authors));
                template = template.replace('###organisations###', checkData(organisations, lth_solr_messages.organisations));
                template = template.replace('###externalOrganisations###', checkData(externalOrganisations));
                template = template.replace('###publicationType###', checkData(publicationType, lth_solr_messages.type));
                template = template.replace('###language###', checkData(language, lth_solr_messages.language));
                template = template.replace('###publicationDateYear###', checkData(publicationDateYear, lth_solr_messages.publicationDateYear));
                template = template.replace('###keywordsUser###', checkData(keywords, lth_solr_messages.keywords_user));
                template = template.replace('###documentUrl###', checkData(documentUrl, lth_solr_messages.fulltext, '', true));
                template = template.replace('###supervisorName###', checkData(supervisorName, lth_solr_messages.supervisor));
                template = template.replace('###bibtex###', checkData(bibtex));
                
                
                                
                $('#page_title h1').text(documentTitle).css('max-width','650px');
                $('#page_title h1').after('<h3>' + publicationType + '</h3>');
                $('#lth_solr_container').html(template);
                if(d.data[0]==="") {
                    $("#lthsolrAbstract").remove();
                }
            }
            if(abstract.length > 500) {
                $('.textblock').addClass('less-content');
                $('.textblock').after('<div><a href="javascript:" id="toggle-link"><i class="fa fa-angle-right"></i>' + lth_solr_messages.more + '</a></div>');

                $('#toggle-link').on('click', function(event) {
                    event.preventDefault();
                    if ( $('.textblock').hasClass('less-content') ) {
                        $('.textblock').removeClass('less-content');
                        $(this).html('<i class="fa fa-angle-up"></i>'+lth_solr_messages.close);
                    } else {
                        $('.textblock').addClass('less-content');
                        $(this).html('<i class="fa fa-angle-right"></i>' + lth_solr_messages.more);
                    }
                });
            }
        }
    });
}


function bibtexFormat(label, input)
{
    if(input) {
        return label + ' = "' + input + '",<br />';
    }
    return '';
}


function showPublication()
{
    //var lth_solr_staffdetailpage = $('#lth_solr_staffdetailpage').val();
    //var lth_solr_projectdetailpage = $('#lth_solr_projectdetailpage').val();
    var syslang = $('html').attr('lang');
    var id,title,abstract,authorId,authorExternal,authorName,authorOrganisation,authorReverseName,authorReverseNameShort,organisationName,organisationId;
    var organisationSourceId,externalOrganisations,keywords_uka,keywords_user,language,pages,numberOfPages,journalTitle,volume,journalNumber;
    var bibtex,cite,doi,electronicIsbns,edition,issn,peerReview,placeOfPublication,event,eventCity,eventCountry;
    var printIsbns,publicationStatus,publicationDateYear,publicationDateMonth,publicationDateDay,publicationType,publicationTypeUri,publisher,supervisors;
    var attachment='',attachmentLimitedVisibility,attachmentMimeType,attachmentSize,attachmentTitle,attachmentUrl,hostPublicationTitle;
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'showPublication',
            term : $('#lth_solr_uuid').val(),
            syslang : syslang,
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: 'json',
        beforeSend: function () {
            $('#lth_solr_container').append('<div class="loader"></div>');
        },
        success: function(d) {
            $('#lth_solr_container').find('.loader').remove();
            if(d.data) {
                var template = $('#solrTemplate').html();
                if(d.data.abstract) abstract = d.data.abstract;
                
                var detailLink = '';
                if(window.location.href.indexOf('/visa/') > 0) {
                    detailLink = window.location.href.split('/visa').shift() + '/visa/';
                } else if(window.location.href.indexOf('/show/') > 0) {
                    detailLink = window.location.href.split('/show').shift()+ '/show/';
                }

                attachmentLimitedVisibility = d.data.attachmentLimitedVisibility;
                attachmentMimeType = d.data.attachmentMimeType;
                attachmentSize = d.data.attachmentSize;
                attachmentTitle = d.data.attachmentTitle;
                attachmentUrl = d.data.attachmentUrl;
                authorId = d.data.authorId;
                authorExternal = d.data.authorExternal;
                authorId = d.data.authorId;
                authorName = d.data.authorName;
                authorOrganisation = d.data.authorOrganisation;
                authorReverseName = d.data.authorReverseName;
                authorReverseNameShort = d.data.authorReverseNameShort;
                bibtex = d.data.bibtex;
                cite = d.data.cite;
                doi = d.data.doi;
                edition = d.data.edition;
                electronicIsbns = d.data.electronicIsbns;
                event = d.data.event;
                eventCity = d.data.eventCity;
                eventCountry = d.data.eventCountry;
                externalOrganisations = d.data.externalOrganisations;
                id = d.data.id;
                hostPublicationTitle = d.data.hostPublicationTitle;
                issn = d.data.issn;
                journalNumber = d.data.journalNumber;
                journalTitle = d.data.journalTitle;
                keywords_uka = d.data.keywords_uka;
                keywords_user = d.data.keywords_user;
                language = titleCase(d.data.language);
                numberOfPages = d.data.numberOfPages;
                organisationName = d.data.organisationName;
                organisationId = d.data.organisationId;
                organisationSourceId = d.data.organisationSourceId;
                pages = d.data.pages;
                peerReview = d.data.peerReview;
                placeOfPublication = d.data.placeOfPublication;
                printIsbns = d.data.printIsbns;
                publicationDateYear = d.data.publicationDateYear;
                publicationDateMonth = d.data.publicationDateMonth;
                publicationDateDay = d.data.publicationDateDay;
                publicationStatus = d.data.publicationStatus;
                publicationType = d.data.publicationType;
                publicationTypeUri = d.data.publicationTypeUri;
                publisher = d.data.publisher;
                supervisors = d.data.supervisors;
                title = d.data.title;
                volume = d.data.volume;
                
                var organisations = '';
                var path = window.location.href.split('(').shift().split('/');
                path.pop();
                path = path.join('/');
                
                var authors = '';
                if(authorName) {
                    var authorNameArray = authorName.split(',');
                    var authorIdArray = authorId.split(',');
                    var authorExternalArray = authorExternal.split(',');
                    for(var i = 0; i < authorNameArray.length; i++) {
                        if(authors) {
                            authors += ', ';
                        }

                        if(authorIdArray[i] && authorExternalArray[i]==0) {
                            authors += '<a href="' + detailLink + authorNameArray[i].replace(' ','-') + '(' + authorIdArray[i] + ')(author)">' + authorNameArray[i] + '</a>';
                        } else {
                            authors += authorNameArray[i];
                        }
                    }
                }
                
                if(eventCity) {
                    event = eventCity;
                }
                if(eventCity && eventCountry) {
                    event += ', ' + eventCountry;
                }
                
                if(organisationSourceId) {
                   organisations = '<a href="' + detailLink + organisationName + '('+ organisationSourceId + ')(department)">' + organisationName + '</a>';
                } else {
                    organisations = organisationName;
                }
                
                if(keywords_user) {
                    for(var i = 0; i < keywords_user.length; i++) {
                        //console.log(keywords_user[i]);
                    }
                }
                
                //attachment
                if(attachmentUrl || doi) {
                    if(attachmentLimitedVisibility==='FREE') {
                        attachment = '<i class="fa fa-unlock"></i>';
                    } else if(attachmentLimitedVisibility==='CAMPUS') {
                        attachment = '<i class="fa fa-lock"></i>';
                    }
                    if(attachmentUrl) {
                        attachment = checkData(attachment + '<a href="' + attachmentUrl + '">' + attachmentTitle + '</a>',lth_solr_messages.attachments);
                    } else {
                        attachment = checkData(attachment + '<a href="' + doi + '">' + doi + '</a>',lth_solr_messages.doi);
                    }
                }

                template = template.replace('###abstract###', checkData(abstract, lth_solr_messages.abstract));
                template = template.replace('###attachment###', attachment)
                template = template.replace(/###authors###/g, checkData(authors, lth_solr_messages.authors));
                template = template.replace('###edition###', checkData(edition, lth_solr_messages.edition));
                template = template.replace('###electronicIsbns###', checkData(electronicIsbns, lth_solr_messages.electronicIsbns));
                template = template.replace('###event###', checkData(event, lth_solr_messages.event));
                template = template.replace('###externalOrganisations###', checkData(externalOrganisations, lth_solr_messages.externalOrganisations));
                template = template.replace('###hostPublicationTitle###', checkData(hostPublicationTitle, lth_solr_messages.hostPublicationTitle));
                template = template.replace('###journalTitle###', checkData(journalTitle, lth_solr_messages.journalTitle));
                template = template.replace('###journalNumber###', checkData(journalNumber, lth_solr_messages.journalNumber));
                template = template.replace('###keywords_uka###', checkData(keywords_uka, lth_solr_messages.keywords_uka));
                template = template.replace('###keywords_user###', checkData(keywords_user, lth_solr_messages.keywords_user));
                template = template.replace('###language###', checkData(language, lth_solr_messages.language));
                template = template.replace('###organisations###', checkData(organisations, lth_solr_messages.organisations));
                template = template.replace('###pages###', checkData(pages, lth_solr_messages.pages));
                template = template.replace('###numberOfPages###', checkData(numberOfPages, lth_solr_messages.numberOfPages));
                template = template.replace('###publicationStatus###', checkData(publicationStatus + '-' + publicationDateYear + ' ' + publicationDateMonth + ' ' + publicationDateDay, lth_solr_messages.publicationStatus));
                template = template.replace('###peerReview###', checkData(peerReview, lth_solr_messages.peerReview, syslang));
                template = template.replace('###printIsbns###', checkData(printIsbns, lth_solr_messages.printIsbns));
                template = template.replace('###placeOfPublication###', checkData(firstToUpperCase(placeOfPublication), lth_solr_messages.placeOfPublication));
                template = template.replace('###publisher###', checkData(publisher, lth_solr_messages.publisher));
                template = template.replace('###supervisors###', checkData(supervisors, lth_solr_messages.supervisors, syslang));
                template = template.replace(/###title###/g, title);
                template = template.replace('###volume###', checkData(volume, lth_solr_messages.volume));

                //bibtex and cite
                template = template.replace('###bibtex###', bibtex);
                template = template.replace('###cite###', cite);
                
                $('#page_title h1').text(d.data.title).css('max-width','650px');
                $('#page_title h1').after('<h3>' + d.data.publicationType + '</h3>');
                $('#lth_solr_container').html(template);
                /*if(abstract==="") {
                    $("#lthsolrAbstract").remove();
                }*/
                //
                if(d.data.abstract.length > 500) {
                    $('.textblock').addClass('less-content');
                    $('.textblock').after('<div><a href="javascript:" id="toggle-link"><i class="fa fa-angle-right"></i>' + lth_solr_messages.more + '</a></div>');
                    
                    $('#toggle-link').on('click', function(event) {
                        event.preventDefault();
                        if ( $('.textblock').hasClass('less-content') ) {
                            $('.textblock').removeClass('less-content');
                            $(this).html('<i class="fa fa-angle-up"></i>'+lth_solr_messages.close);
                        } else {
                            $('.textblock').addClass('less-content');
                            $(this).html('<i class="fa fa-angle-right"></i>' + lth_solr_messages.more);
                        }
                    });
                }
            }
        }
    });
}


function firstToUpperCase( str )
{
    if(str) return str.substr(0, 1).toUpperCase() + str.substr(1);
}


function stdWrap(input)
{
    if(input) {
        input = '<p>' + input + '</p>';
    }
    return input;
}


function addVol(input)
{
    if(input) {
        input = ", vol " + input;
    }
    return input;
}


function addDot(input)
{
    if(input) {
        input = input + ".";
    }
    return input;
}


function addSemicolon(input)
{
    if(input) {
        input = input + ";";
    }
    return input;
}


function addColon(input)
{
    if(input) {
        input = input + ":";
    }
    return input;
}


function addSpace(input)
{
    if(input) {
        input = input + " ";
    }
    return input;
}


function addPp(input)
{
    if(input) {
        input = ", pp. " + input;
    }
    return input;
}


function addDoi(input)
{
    if(input) {
        input = " DOI: " + input;
    }
    return input;
}


function addComma(input)
{
    if(input) {
        input = ", " + input;
    }
    return input;
}


function addItalic(input)
{
    if(input) {
        input = " <i>" + input + "</i>";
    }
    return input;
}


function addParenthesis(input)
{
    if(input) {
        input = " (" + input + ")";
    }
    return input;
}


function fixResearchOutput(input)
{
    if(input) {
        input = input.split('/researchoutputtypes/')[1].replace('/', ' > ').replace('contributiontojournal', 'Contribution to journal').replace('article','Article');
    }
    return input;
}


function listProjects(tableStart, query, more)
{
    var syslang = $('#lth_solr_syslang').val();
    var scope = $('#lth_solr_scope').val();
    var tableLength = $('#lth_solr_no_items').val();
    var id, title, participants, projectStartDate, projectEndDate, projectStatus;
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'listProjects',
            tableStart: tableStart,
            tableLength : tableLength,
            pid : $('#pid').val(),
            pageid : $('body').attr('id'),
            scope : scope,
            syslang : syslang,
            query: query,
            addPeople : $('#addPeople').val(),
            sid : Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            if(!more) {
                $('#lthsolr_projects_container div').not('#lthsolr_projects_header').remove().append('<img class="lthsolr_loader" src="/fileadmin/templates/images/ajax-loader.gif" />');
            }
            //$('#lthsolr_all').remove();
            $('.lthsolr_more').replaceWith('<img class="lthsolr_loader" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            if(d.data) {
                
                var projectDetailPage = 'visa';
                if(syslang=='en') {
                    projectDetailPage = 'show';
                }
                
                $.each( d.data, function( key, aData ) {
                    id = aData.id;
                    title = aData.title;
                    if(title == "") {
                        title = 'untitled';
                    }
                    var path = '';
                    if(window.location.href.indexOf('(') > 0) {
                        path = window.location.href.split('(').shift().split('/');
                        path.pop();
                        path = path.join('/');
                    } else if(window.location.href.indexOf('?') > 0) {
                        path = window.location.href.split('?').shift().split('/');
                        path.pop();
                        path = path.join('/');
                    } else {
                        path = window.location.href + projectDetailPage;
                    }

                    title = '<a href="' + path + '/' + title.replace(/[^\w\s-]/g,'').replace(/ /g,'-').toLowerCase() + '(' + id + ')">' + title + '</a>';
                    participants = aData.participants;
                    projectStartDate = aData.projectStartDate;
                    projectEndDate = aData.projectEndDate;
                    projectStatus = aData.projectStatus;
                    
                    var template = $('#solrProjectTemplate').html();

                    template = template.replace('###id###', id);
                    template = template.replace('###title###', title);
                    template = template.replace('###participants###', participants);
                    template = template.replace('###projectStartDate###', projectStartDate);
                    template = template.replace('###projectEndDate###', projectEndDate);
                    template = template.replace('###projectStatus###', projectStatus);
                    
                    $('#lthsolr_projects_container').append(template);
                });
                
                $('.lthsolr_loader').remove();

                $('#lthsolr_projects_header').html('<div style="float:left;">1-' + maxLength(parseInt(tableStart),parseInt(tableLength),parseInt(d.numFound)) + ' ' + lth_solr_messages.of + ' ' + d.numFound + '</div>');

                if((parseInt(tableStart) + parseInt(tableLength)) < d.numFound) {
                    var tempMore = '<div style="margin-top:20px;" class="lthsolr_more"><a href="javascript:" onclick="listProjects(' + (parseInt(tableStart) + parseInt(tableLength)) + ',\'\',\'more\');">NEXT ' + tableLength + ' of ' + d.numFound + '</a>';
                    if(d.numFound < 300) {      
                        tempMore += ' | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.numFound + '); listProjects(' + (parseInt(tableStart) + parseInt(tableLength)) + ',\'\',\'more\');">Show all ' + d.numFound + '</a>';
                    }
                    tempMore += '</div>';
                    $('#lthsolr_projects_container').append(tempMore);
                }
                /*if(!mobileCheck()) {
                    $('#lthsolr_projects_container').parent().height($('#lthsolr_publications_container').height());
                    $('#lth_solr_facet_container').height($('#lthsolr_publications_container').height());
                    $('#lthsolr_projects_container, #lth_solr_facet_container').css('float','left');
                }*/
            }
        }
    });
}


function showProject()
{
    var id, title, participants, projectStartDate, projectEndDate, projectStatus, description;
    
    var lth_solr_staffdetailpage = $('#lth_solr_staffdetailpage').val();
    var lth_solr_publicationdetailpage = $('#lth_solr_publicationdetailpage').val();
    
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
            $('#lth_solr_projects_container').html('<img src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            //console.log(d);
            if(d.data) {
                id = d.data.id;
                title = d.data.title;
                if(title == "") {
                    title = 'untitled';
                }
                participants = d.data.participants;
                projectStartDate = d.data.projectStartDate;
                projectEndDate = d.data.projectEndDate;
                projectStatus = d.data.projectStatus;
                description = d.data.description;
                
                var template = $('#solrProjectTemplate').html();

                template = template.replace('###title###', title);
                template = template.replace('###participants###', participants);
                template = template.replace('###projectStartDate###', projectStartDate);
                template = template.replace('###projectEndDate###', projectEndDate);
                template = template.replace('###projectStatus###', projectStatus);
                template = template.replace('###description###', description);

                $('#lth_solr_projects_container').html(template);
                
                if(!description) {
                    $('.more-content').parent().remove();
                }
                    
                $('#page_title h1').text(title);
            }
        }
    });
}


function toggleFacets()
{
    $('.maxlist-more a').on( 'click', function () {
        //console.log($(this).parent().prev());
        $(this).parent().prev().find('.maxlist-hidden').toggle('slow');
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


function showLocation(position)
{
    //alert(position);
    if(position) {
        var positionArray = $('#lthsolr_googlelink').text().split(',');

        var cornerLatitude = parseFloat(55.716760).toFixed(6);
        var cornerLongitude = parseFloat(13.198662).toFixed(6);
        var latitude = position.coords.latitude.toFixed(6); //55.7046601
        var longitude = position.coords.longitude.toFixed(6); //13.191007299999999
        var diffLatitude = (cornerLatitude - latitude) * 7500;
        var diffLongitude = (longitude - cornerLongitude) * 4500;
        //alert(latitude+';'+longitude);
        $('#lthsolr_pinClient').show().css('top',diffLatitude + '%').css('left', diffLongitude + '%');
        
        //Add google maps link
        $('#lthsolr_googlelink').html('').show().html('<a href="https://www.google.com/maps/dir/?api=1&origin='+positionArray[0]+','+positionArray[1]+'&destination='+latitude+','+longitude+'">Link to google maps</a>');
    }
    
    //console.log(diffLatitude+'%, ' + diffLongitude + '%');
}


function errorHandler(err)
{
    if(err.code == 1) {
       alert("Error: Access is denied!");
    }

    else if( err.code == 2) {
       alert("Error: Position is unavailable!");
    }
}


function showStaff()
{
    //corner: 55.716008, 13.206647
    //mypos: 55.7046601,13.191007299999999
    /*if (navigator.geolocation) { 

        navigator.geolocation.getCurrentPosition(showLocation); 

    } else { 

        $('#location').html('Geolocation is not supported by this browser.'); 

    }*/
    var syslang = $('html').attr('lang');
    var tableLength = $('#lth_solr_no_items').val();
    
    $.ajax({
        type : "POST",
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'showStaff',
            tableLength : tableLength,
            pid : $('#pid').val(),
            pageid : $('body').attr('id'),
            scope : $('#lth_solr_scope').val(),
            syslang : syslang,
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: "json",
        beforeSend: function () {
            /*if(lth_solr_staff_pos=='right') {
                $('.grid-23').after('<div id="content_sidebar_wrapper" class="grid-8 omega"><div id="content_sidebar"><h2>Contact</h2></div>');
                var staffContainer = $('#lthsolr_staff_container');
                $('#content_sidebar h2').after(staffContainer);
            }*/
            $('#lthsolr_show_staff_container').append('<img class="lthsolr_loader" id="lthsolr_loader_staff" src="/fileadmin/templates/images/ajax-loader.gif" />');
            //$('#content_navigation').hide(200);
            //$('#text_wrapper').removeClass('grid-23').removeClass('omega').addClass('grid-31');
        },
        success: function(d) {
            //Staff
            if(d.data) {                                 
                $.each( d.data, function( key, aData ) {
                    var intro = '';
                    var template = $('#solrStaffTemplate').html();

                    var uuid = aData.uuid;
                    if(!uuid) uuid = aData.guid;
                    template = template.replace('###id###', uuid);

                    var displayName = aData.firstName + ' ' + aData.lastName;
                    
                    //template = template.replace('###displayName###', display_name);
                    var title, ophone = '', ostreet = '', organisationName = '', ocity = '', organisationPostalAddress = '', phone = '', roomNumber = '', homePage = '', opostal_address = '';

                    template = template.replace("###displayName###", displayName);
                    
                    template = template.replace(/###email###/g, aData.email);

                    if(aData.title) title = aData.title[0];
                    
                    if(aData.organisationName) organisationName = aData.organisationName[0];
                    if(aData.phone) {
                        phone = aData.phone[0];
                    }
                    if(phone) phone = phone.replace('+4646222', '+46 46 222 ').replace(/(.{2}$)/, ' $1');
                    if(aData.mobile) {
                        if(phone) phone += ', ';
                        phone += aData.mobile[0];
                    }
                    
                    //Map
                    //console.log(aData.coordinates.split(', ').pop());
                    if(aData.coordinates) {
                        //position = '55.710466,13.205075';
                        var positionArray = aData.coordinates.split(',');
                        var latitude = parseFloat(positionArray[0]).toFixed(6);
                        var longitude = parseFloat(positionArray[1]).toFixed(6);
                        //55.712544,13.211670
                        var cornerLatitude = parseFloat(55.716760).toFixed(6);
                        var cornerLongitude = parseFloat(13.198662).toFixed(6);//55.716760, 13.198662
                        var diffLatitude = (cornerLatitude - latitude) * 7300;
                        var diffLongitude = (longitude - cornerLongitude) * 4700;

                        $('#lthsolr_map').show();
                        $('#lthsolr_pin').css('top',diffLatitude + '%').css('left', diffLongitude + '%');
                        if(!mobileCheck()) {
                            $('#lthsolr_map').click(function(){
                                $('#mapModal').modal('toggle');
                                $('#lthsolr_modal_pin').css('top',diffLatitude + '%').css('left', diffLongitude + '%');
                                $('.modal-body').append('<a target="_blank" title="Open link to Google maps in new window" href="http://www.google.com/maps/place/'+
                                        latitude+','+longitude+'">Google Maps (new window)</a>');
                            });
                        } else {
                            $('#lthsolr_map').append('<a target="_blank" title="Open link to Google maps in new window" href="http://www.google.com/maps/place/'+
                                        latitude+','+longitude+'">Google Maps (new window)</a>');
                        }
                        
                        if(mobileCheck()) {
                            $('#lthsolr_googlelink').text(aData.coordinates);
                            if(navigator.geolocation){
                                var options = {timeout:60000,latitude:latitude,longitude:longitude};
                                navigator.geolocation.getCurrentPosition(showLocation, errorHandler, options); 
                            }
                        }
                    }

                    //Change page main header
                    $('#page_title h1').text(displayName).append('<h2>'+title+'</h2>');
                    
                    //template = template.replace('###title###', titleCase(title));
                    template = template.replace('###phone###', addBreak(phone));

                    template = template.replace('###organisationName###', organisationName);

                    //template = template.replace('###primaryAffiliation###', aData.primaryAffiliation);

                    /*if(aData[10]) {
                        homePage = lth_solr_messages.personal_homepage + ': <a data-homepage="' + aData[10] + '" href="' + aData[10] + '">' + aData[10] + '</a>';
                    } else if(aData[15]) {
                        homePage = lth_solr_messages.read_more_about + ' ' + display_name;
                    }
                    template = template.replace('###homepage###', '<p>' + homePage + '</p>');
                    */
                    template = template.replace('###homepage###', '');

                    //template = template.replace('###image###', '<div style="height: 100px"><img style="max-height: 100%; max-width: 100%" src="' + aData[11] + '" /></div>');
                    if(!aData.image) {
                        var image = '<div class="lthsolr_noimage align_left" style="width:180px;min-height:167px;"><img style="max-height: 100%; max-width: 100%" src="/typo3conf/ext/lth_solr/res/noimage.gif" /></div>';
                    } else {
                        var image = '<div class="align_left" style="width:180px;min-height:167px;"><img style="max-height: 100%; max-width: 100%" src="' + aData.image + '" /></div>';
                    }
                    template = template.replace('###image###', image);
                    
                    if(aData.intro) intro = aData.intro.replace('\n','<br />');
                    template = template.replace('###lth_solr_intro###', intro);

                    roomNumber = aData.roomNumber;
                    if(roomNumber) {
                        roomNumber = '(' + lth_solr_messages.room + ' ' + roomNumber + ')';
                    } else {
                        roomNumber = '';
                    }
                    template = template.replace('###roomNumber###', roomNumber);
                    ophone = aData.organisationPhone;
                    ostreet = aData.organisationStreet;
                    ocity = aData.organisationCity;
                    if(aData.organisationPostalAddress) {
                        organisationPostalAddress = aData.organisationPostalAddress.toString().split('$').join(', ');
                    }
                    template = template.replace('###visitingAddress###', ostreet + ' ' + ocity + addBreak(ophone));
                    template = template.replace('###postalAddress###', addBreak(organisationPostalAddress));
                    
                    $('#lthsolr_show_staff_container').append(template);
                    if(aData.profileInformation) {
                        $('#lthsolr_show_staff_container').append('<div class="lthsolr_profileinformation"><div class="lthsolr_filler"></div>' + aData.profileInformation + '</div>');
                    }
                });
                $('#lthsolr_loader_staff').remove();
            } else {
                $('#lthsolr_loader_staff').remove();
            }

            //Publications
            //console.log(d.publicationData.length);
            /*var pages, publicationDate, journalTitle, title;
            var publicationDetailPage = $('#lth_solr_publicationdetailpage').val();
            if(syslang=='en') {
                publicationDetailPage += 'show';
            } else {
                publicationDetailPage += 'visa';
            }
                
            var lastGroupValue = '';
            if(d.publicationData.length > 0) {
                $('#lthsolr_publications_header').html('<h3>' + lth_solr_messages.publications + '</h3>');
                loopPublications(d, '', '', '', '0', '20');
                $.each( d.publicationData, function( key, aData ) {
                    var template = $('#solrPublicationTemplate').html();

                    pages = '';
                    publicationDate = '';
                    journalTitle = '';
                    
                        if(lastGroupValue!=aData.publicationDateYear) {
                            $('#lthsolr_publications_container').append('<div class="lthsolr_publication_row"><h3>'+aData.publicationDateYear+'</h3></div>');
                        }
                    
                    var id = aData.id;
                    if(aData.documentTitle) {
                        title = aData.documentTitle;
                        if(publicationDetailPage) {
                            title = '<a href="' + publicationDetailPage + '/' + encodeURIComponent(title.replace(/ /g,'-')) + '(' + id + ')">' + title + '</a>';
                        }
                        title = '<b>'+title+'</b>';
                    } else {
                        title = 'untitled';
                    }
                    if(aData.publicationDateYear) publicationDate = aData.publicationDateYear;
                    if(aData.publicationDateMonth) publicationDate += '-'+aData.publicationDateMonth;
                    if(aData.publicationDateDay) publicationDate += '-'+aData.publicationDateDay;
                    if(aData.pages) {
                        if(syslang=='en') {
                            pages = 'p. ' + aData.pages;
                        } else {
                            pages = 's. ' + aData.pages;
                        }
                    }
                    if(aData.journalTitle) {
                        if(syslang=='en') {
                            journalTitle = 'In: ' + aData.journalTitle;
                        } else {
                            journalTitle = 'I: ' + aData.journalTitle;
                        }
                    }
                    if(aData.journalTitle && aData.journalNumber) journalTitle += ' ' + aData.journalNumber;

                    template = template.replace('###id###', id);
                    template = template.replace('###title###', title);
                    template = template.replace('###authorName###', aData.authorName);
                    template = template.replace('###publicationType###', aData.publicationType);
                    template = template.replace('###publicationDate###', publicationDate);
                    template = template.replace('###pages###', pages);
                    template = template.replace('###journalTitle###', journalTitle);
                    
                    $('#lthsolr_publications_container').append(template);
                    
                    lastGroupValue = aData.publicationDateYear;
                });
                
                $('#lthsolr_loader_publication').remove();

                $('#lthsolr_publications_header h3').append(' (1-' + maxLength(parseInt(tableStartPublications),parseInt(tableLength),parseInt(d.publicationNumFound)) + ' of ' + d.publicationNumFound + ')');
                
                if((parseInt(tableStartPublications) + parseInt(tableLength)) < d.publicationNumFound) {
                    $('#lthsolr_publications_container').append('<div style="margin-top:20px;" class="lthsolr_more">\n\
                    <button class="btn btn-default btn-lg btn-block" style="height:30px;" \n\
                    onclick="listPublications(' + (parseInt(tableStartPublications) + parseInt(tableLength)) + ',\'\',\'\',\'publicationYear\',\'more\',\'\');">' + 
                    lth_solr_messages.show_more + ' ' + lth_solr_messages.publications + 
                    ' <span class="glyphicon glyphicon-chevron-down"></span></button></div>');
                }
            } else {
                $('#lthsolr_loader_publication').remove();
            }
            */
           
            //Projects
            //console.log(d.projectData.length);
            /*if(d.projectData.length > 0) {
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
            }*/
            
            /*$('.lthsolr_publication_row').on( 'click', function () {
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
            });*/
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
}


String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}


function checkData(data, label, syslang, isLink)
{
    var content = '';

    if(data && data!='') {
        if(data=='true' && syslang=='sv') data = 'Ja';
        if(data=='true' && syslang=='en') data = 'Yes'
        if(data=='false' && syslang=='sv') data = 'Nej'
        if(data=='false' && syslang=='en') data = 'No'
        content = '<p>';
        if(label) content += '<b>' + label + '</b><br/>';
        if(isLink) content += '<a href="'+data+'">';
        content += data;
        if(isLink) content += '</a>';
        content += '</p>';
        return content;
    } else {
        return '';
    }
}


function createFacetClick(listType, sorting)
{
    $('.lth_solr_facet').click(function() {
        if(listType==='listStaff') {
            listStaff(0, getFacets(), $('#lthsolr_staff_filter').val().trim(),false,false);
        } else if(listType==='listPublications') {
            listPublications(0, getFacets(), $('#lthsolr_publications_filter').val().trim(),sorting,false,'');
        } else if(listType==='listStudentPapers') {
            listStudentPapers(0, getFacets(), $('#lthsolr_studentpapers_filter').val().trim(),false,false);
        }
    });
}


function getFacets()
{
    var facet = [];
    $("#lth_solr_facet_container input[type=checkbox]").each(function() {
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