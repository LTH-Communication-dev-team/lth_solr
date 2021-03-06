var lthClassesToShow = Array('dt-buttons');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
   
$(document).ready(function() {
    var action = $('#lth_solr_action').val();
    if(action == 'searchLong') {
        //widget($('#query').val());
        searchLong($('#lth_solr_query').val(), 0, 0, 0, false, false);
    } else if(action === 'listStaff') {
        listStaff(0);
        $(".refine").click(function(){
            $("#lth_solr_facet_container").toggle(500);
            //$("#lthsolr_staff_container").toggleClass('expand', 500);
        });
    } else if(action == 'showStaff') {
        showStaff();
        listPublications(0,'','','publicationYear',0,'','listPublications');
    } else if(action === 'listPublications' || action === 'listComingDissertations') {
        listPublications(0,'','','publicationYear',0,'',action);
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
        listPublications(0,'','','publicationYear',0,'','listPublications');
    } else if($('#lth_solr_action').val() == 'listCompare') {
        listCompare('listCompare');
    }
    
    if($('#lth_solr_action').val() == 'listTagCloud') {
        listTagCloud();
    }
    
    if($('#lth_solr_action').val() == 'listJobs') {
        listJobs();
    } else if($('#lth_solr_action').val() == 'showJob') {
        showJob();
    }
    
    if($('#lth_solr_action').val() == 'listCourses') {
        listCourses();
    } else if($('#lth_solr_action').val() == 'showCourse') {
        showCourse();
    }
    
    if($('#lth_solr_action').val() == 'listStatistics') {
        listStatistics();
    }
    
    if($('#lth_solr_action').val() == 'listOrganisation') {
        listOrganisation('');
    } else if($('#lth_solr_action').val() == 'listOrganisationStaff') {
        listOrganisationStaff(JSON.stringify(["firstLetterExact###a"]), '', 0, '');
    } else if($('#lth_solr_action').val() == 'listSingleOrganisationStaff') {
        listOrganisationStaff('', '', 0, '');
    } else if($('#lth_solr_action').val() == 'listOrganisationRoles') {
        listOrganisationRoles('');
    } else if($('#lth_solr_action').val() == 'showStaffNovo') {
        showStaffNovo();
    } else if($('#lth_solr_action').val() == 'listOrganisationPublications') {
        listOrganisationPublications('', '', 0, '');
    } else if($('#lth_solr_action').val() == 'listOrganisationStudentPapers') {
        listOrganisationStudentPapers('', '', 0, '');
    } else if($('#lth_solr_action').val() == 'showStudentPaperNovo') {
        showStudentPaperNovo();
    } else if($('#lth_solr_action').val() == 'latestDissertationsStudentPapers') {
        latestDissertationsStudentPapers(0);
    } else if($('#lth_solr_action').val() == 'listOrganisationProjects') {
        listOrganisationProjects('', '', 0);
    } else if($('#lth_solr_action').val() == 'showProjectNovo') {
        showProjectNovo();
    } else if($('#lth_solr_action').val() == 'showPublicationNovo') {
        showPublicationNovo();
    }
    
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
    
    $('#lth_solr_tools').click(function() {
        $('#lth_solr_hidden_tools').toggle('slow');
    });
   
});


function listCourses()
{
    var syslang = $('html').attr('lang');
    var round = $('#lth_solr_round').val();
    var courseCode, coursePlace,courseTitle,credit,id,link;

    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: 'listCourses',
            syslang: syslang,
            dataSettings: {
                round: round,
                syslang: syslang,
                pageid: $('body').attr('id')
            },
            sid: Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            $('#lthsolr_course_container').append('<div class="lthPackageLoader"></div>');
        },
        success: function(d) {
            $('.lthPackageLoader').remove();
            
            if(d.data) {
                $.each( d.data, function( key, aData ) {
                    coursePlace = '';
                    courseTitle = '';
                    credit = '';
                    id = '';

                    if(aData.courseCode) courseCode = aData.courseCode;
                    if(aData.coursePlace) coursePlace = aData.coursePlace;
                    if(aData.courseTitle) courseTitle = aData.courseTitle;
                    if(aData.credit) credit = aData.credit;
                    if(aData.id) id = aData.id;
                    
                    if(syslang==='sv') {
                        link = 'visa/' + encodeURIComponent(courseTitle) + '('+courseCode+')';
                    } else {
                        link = 'show/' + encodeURIComponent(courseTitle) + '('+courseCode+')';
                    }
                    
                    var template = $('#solrCourseTemplate').html();
                    template = template.replace('###credit###', credit);
                    template = template.replace('###courseTitle###', courseTitle);
                    template = template.replace('###coursePlace###', coursePlace);
                    template = template.replace('###link###', link);
                    $('#lthsolr_course_container').append(template);
                });
            }
        }
    });
}


function showCourse()
{
    var syslang = $('html').attr('lang');
    var scope = $('#lth_solr_scope').val();
    var roundId = $('#lth_solr_round').val();
    var department,courseCode,courseTitle,credit,homepage,ratingScale;
    var courseForkunKrav, courseSlutDatum, courseSlutDatum, coursePace;

    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: 'showCourse',
            syslang: syslang,
            dataSettings: {
                syslang: syslang,
                scope: scope,
                roundId: roundId,
                pageid: $('body').attr('id')
            },
            sid: Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            
        },
        success: function(d) {
            
            
            //$('.article').html('<div class="lthPackageLoader"></div>');
            //$('.lthsolr_loader').remove();
            if(d.data) {
                department = '';
                courseCode = '';
                courseTitle = '';
                credit = '';
                homepage = '';
                ratingScale = '';
                courseForkunKrav = '';
                courseSlutDatum = '';
                courseSlutDatum = '';
                coursePace = '';
                if(d.data.department) department = d.data.department;
                if(d.data.courseCode) courseCode = d.data.courseCode;
                if(d.data.courseTitle) courseTitle = d.data.courseTitle;
                if(d.data.credit) credit = d.data.credit;
                if(d.data.homepage) homepage = d.data.homepage;
                if(d.data.ratingScale) ratingScale = d.data.ratingScale;
                if(d.data.courseForkunKrav) courseForkunKrav = d.data.courseForkunKrav;
                if(d.data.courseSlutDatum) courseSlutDatum = d.data.courseSlutDatum;
                if(d.data.courseSlutDatum) courseSlutDatum = d.data.courseSlutDatum;
                if(d.data.coursePace) coursePace = d.data.coursePace;
                //console.log(d.data.abstract);
                $('h1').text(courseTitle).attr('style', 'margin-bottom:18px !important;max-width:650px;');
                //$('.lthsolr_job_apply_button').attr('href',loginAndApplyURI).text(lth_solr_messages.applyButtonText).show();
                //$('.breadcrumb li:last').removeClass('active').wrapInner('<a href="/'+lth_solr_messages.job+'/"></a>');
                $('.breadcrumb').append('<li class="breadcrumb-item active">'+courseTitle+'</li>');
                $('#lthsolr_course_container').append('<p><b>Kursplan</b><br/><a href="https://kurser.lth.se/kursplaner/fk_2019_vt/'+courseCode.toUpperCase()+'.pdf">Kursplan i PDF-format</a></p>');
                if(courseForkunKrav) $('#lthsolr_course_container').append('<p><b>Förkunskapskrav</b><br/>'+courseForkunKrav.split('|').pop()+'</p>');
                //if(department) $('#lthsolr_course_container').append(department);
                //if(courseCode) $('#lthsolr_course_container').append(courseCode);
                //if(courseTitle) $('#lthsolr_course_container').append(courseTitle);
                //if(credit) $('#lthsolr_course_container').append(credit);
                //if(homepage) $('#lthsolr_course_container').append(homepage);
                //if(ratingScale) $('#lthsolr_course_container').append(ratingScale);
                if(coursePace) $('#lthsolr_course_container').append('<p><b>Kurstakt</b><br/>'+coursePace+'</p>');
                if(courseSlutDatum) $('#lthsolr_course_container').append(courseSlutDatum);
                if(courseSlutDatum) $('#lthsolr_course_container').append(courseSlutDatum);
            }
        }
    });
}


function listOrganisation(query)
{
    var scope = $('#lth_solr_scope').val();
    var sysLang = $('html').attr('lang');
    var homepage, id, mailDelivery, organisationCity, organisationParent, organisationPhone, organisationPostalAddress;
    var organisationSourceId, organisationStreet, organisationTitle;
    var listPage = 'lista';
    if(sysLang==='en') listPage = 'list';
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: 'listOrganisation',
            dataSettings: {
                pageid: $('body').attr('id'),
                query: query,
                scope: scope,
                syslang: sysLang
            },
            sid: Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            $('#lthsolr_organisation_container > div > section').empty();
            $('#lthsolr_organisation_container > div > section').append(getSpinner(sysLang));
        },
        success: function(d) {
            $('.spinner').remove();
            if(d.data) {
                if($('.lth_solr_culdesac').length===0)  {
                    $('#lthsolr_organisation_filter').addClass('lth_solr_culdesac').keyup(function() {
                        listOrganisation($(this).val().trim());
                    });
                }
                
                $.each( d.data, function( key, aData ) {
                    homepage = '';
                    id = '';
                    mailDelivery = '';
                    organisationCity = '';
                    organisationParent = '';
                    organisationPhone = '';
                    organisationPostalAddress = '';
                    organisationSourceId = '';
                    organisationStreet = '';
                    organisationTitle = '';
                    
                    if(aData.id) id = aData.id;
                    if(aData.homepage && sysLang==='sv') homepage = '<a href="'+aData.homepage+'">Besök webbplats</a>';
                    if(aData.homepage && sysLang==='en') homepage = '<a href="'+aData.homepage+'">Visit website</a>';
                    if(aData.mailDelivery) mailDelivery = aData.mailDelivery; 
                    if(aData.organisationCity) organisationCity = aData.organisationCity; 
                    if(aData.organisationParent) organisationParent = aData.organisationParent; 
                    if(aData.organisationPhone[0] && aData.organisationPhone[0] !== ' NULL') organisationPhone = 'Tel: <a href="tel:'+aData.organisationPhone[0]+'">'+formatPhone(aData.organisationPhone[0])+'</a><br/>'; 
                    if(aData.organisationPostalAddress) organisationPostalAddress = aData.organisationPostalAddress; 
                    if(aData.organisationSourceId) organisationSourceId = aData.organisationSourceId; 
                    if(aData.organisationStreet) organisationStreet = aData.organisationStreet;
                    if(aData.organisationTitle) organisationTitle = aData.organisationTitle;
                    
                    var template = $('#solrOrganisationTemplate').html();
                    
                    template = template.replace('###id###', id);
                    template = template.replace('###organisationTitle###', organisationTitle);
                    template = template.replace(/###phone###/g, organisationPhone);
                    template = template.replace(/###homepage###/g, homepage);
                    template = template.replace(/###address###/g, organisationStreet);
                    
                    /*if(syslang==='sv') {
                        link = 'visa/'+encodeURI(organisationTitle+'('+id+')');
                    } else {
                        link = 'show/'+encodeURI(organisationTitle+'('+id+')');
                    }*/
                    
                    template = template.replace('###link###', listPage + '/' + encodeURI(organisationTitle)+'/');
                    
                    $('#lthsolr_organisation_container > div > section').append(template);
                });
            }
        }
    });
}


function listOrganisationPublications(facet, query, tableStart, more)
{
    var facetChoice = $('#lth_solr_facetchoice').val();
    var sysLang = $('html').attr('lang');
    var scope = $('#lth_solr_scope').val();
    var moreText = 'Visa fler resultat';
    var errorText = 'Skriv minst 3 tecken';
    var path = '';
    var publicationDetailPage = 'visa';
    if(sysLang==='en') {
        errorText = 'Write at leat 3 characters';
        moreText = 'Show more results';
        publicationDetailPage = 'show';
    }
    var path = window.location.href + publicationDetailPage;
    var tableLength = 50;
    
    var i = 0;
    var authorName, count, facetHeader, more, content, numberOfPages, publicationDate, journalTitle, title, placeOfPublication, authorName, openAccessPermission;
    var id, publisher, hostPublicationTitle, volume, pages, articleNumber, bibliographicalNote;
    var electronicIsbn, electronicVersionFileURL, electronicVersionLicenseType;
    var electronicVersion, link, portalUrl, publicationStatus, publicationType;
    
    if($('#lth_solr_query').val().trim().length > 2) {
        query = $('#lth_solr_query').val().trim();
        $('#lthsolr_organisation_filter').val(query);
        $('#lth_solr_query').val('');
    }
    
    var facetVal, count;
    if(!facet) facet = '';
    var detailPage = 'visa';
    if(sysLang==='en') detailPage = 'show';
    //var exportArray = ["firstName","lastName","title","phone","email","organisationName","homepage","roomNumber","mobile"];
    if($('.lth_solr_culdesac').length === 0) {
        $('#lthSolrClear').click(function() {
            $('#lthsolr_organisation_filter').val('');
            $('#lthSolrFacetShow').removeClass('active');
            $('.lth_solr_facet').removeClass('active');
            listOrganisationPublications(getActiveFacets(), $('#lthsolr_organisation_filter').val(),0,'');
        });
        //$('#lthSolrFacetShow').addClass('active');
        $('#lthSolrSearch').addClass('lth_solr_culdesac').click(function() {
            //console.log('???');
            if($('#lthsolr_organisation_filter').val().trim().length > 2) {
                $('#facet_container .nav-link').removeClass('active');
                listOrganisationPublications(getActiveFacets(), $('#lthsolr_organisation_filter').val().trim(),0,'');
            } else {
                alert(errorText);
            }
            
            //$('#facet_container .nav-link:eq(0)').addClass('active');
        });
        $(document).mouseup(function(e){
            //e.stopPropagation();
            var container = $(".lthSolrFacetGroup");

            if(!container.is(e.target) && container.has(e.target).length === 0){
                //console.log(container);
                //if(container.is(":visible")) container.removeClass("show");
                $('.lthSolrFacetDropdown').removeClass("show");
            }
        });
        //Typeahed
        //http://www.runningcoder.org/jquerytypeahead/
        $('#lthsolr_organisation_filter').typeahead({
            hint: true,
            highlight: true,
            minLength: 3
        },
        {
            source: function (query, processSync, processAsync) {
              //processSync(['This suggestion appears immediately', 'This one too']);
                return $.ajax({
                    type : 'POST',
                    url: "index.php", 
                    //data: {query: query},
                    data: {
                        eID : 'lth_solr',
                        action: 'listOrganisationPublications',
                        sid : Math.random(),
                        dataSettings: {
                            scope: scope,
                            tableLength: 5,
                            query: query,
                            facet: function() {
                                var myfacet = [];
                                //console.log($(".lth_solr_facet.active").length);
                                $(".lth_solr_facet.active").each(function() {
                                    myfacet.push($(this).val());
                                });
                                if(facet.length > 0) {
                                    return JSON.stringify(myfacet);
                                }
                            },
                            facetChoice: facetChoice,
                        }
                    },
                    dataType: 'json',
                    success: function (d) {
                      // in this example, json is simply an array of strings
                        //return processAsync(json);
                        if(d.data) {
                            jsonObj = [];
                            $.each(d.data, function(key, aData) {
                                //console.log(aData);
                                if(aData.documentTitle) {
                                    item = {};
                                    item ["id"] = aData.id;
                                    item ["label"] = aData.documentTitle + ', ' + aData.authorName;
                                    item ["value"] = aData.portalUrl;
                                    item ["type"] = '???';
                                    jsonObj.push(item);
                                }
                            });
                        }
                        //response( jsonObj );
                        //console.log(jsonObj);
                        processAsync(jsonObj);
                    }
                });
            },
            limit: 12,
            display: function (suggestion) {
                return suggestion.label;
            },
            async: true,
        }).on('typeahead:select', function(event, select) {
            //window.location.href = $('#header-search-form').find('form').attr('action') + '?term='+select.id;
            window.location.href = path + '/' + select.value.split('/').pop().split('(').shift();
        });
    }
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: 'listOrganisationPublications',
            dataSettings: {
                facet: facet,
                facetChoice: facetChoice,
                pageid: $('body').attr('id').replace('p',''),
                query: query,
                scope: scope,
                syslang: sysLang,
                tableLength: tableLength,
                tableStart: tableStart
            },
            sid : Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            $('#lthsolr_organisation_container > div > section').empty();
            $('#lthsolr_organisation_container > div > section').append(getSpinner(sysLang));
        },
        success: function(d) {
            $('.spinner').remove();
            
            //var staffDetailPage = 'visa';
            var allText = 'Alla';
            //var restText = 'Övriga';
            if(sysLang=='en') {
                //staffDetailPage = 'show';
                allText = 'All';
                //var restText = 'Other';
            }

            if(d.data) {
                                
                if(d.facet) {
                    if($('#facetContainer').length === 0) {
                        $('#lthsolr_organisation_container').before('<div class="row"><div id="facetContainer" class="col-12" style="display:none;"><div class="btn-group"></div></div>');

                        //var facetNavActiveClass = '';
                        var i = 0;
                        //var totalCount = 0;
                        var oldFacetHeader = '';
                        $.each( d.facet, function( key, value ) {
                            $.each( value, function( key1, value1 ) {
                                //facetNavActiveClass = '';
                                facetVal = value1[0].toString();
                                count = value1[1];
                                facetHeader = value1[2].toString();
                                if(facetHeader !== oldFacetHeader) {
                                    i++;
                                    $('#facetContainer > .btn-group').append('<div class="btn-group lthSolrFacetGroup">' +
                                            '<a href="#" class="btn-sm dropdown-toggle lthSolrFacetButton" data-toggle="collapse" data-target="#facetSub' + i + '" aria-expanded="false" aria-controls="facetSub' + i + '">' +
                                            facetHeader +
                                            '</a>' + 
                                            '<div class="dropdown-menu lthSolrFacetDropdown" id="facetSub' + i + '">' + 
                                            '<div class="list-group">' +
                                            '</div>' +
                                            '</div>' +
                                            '</div>');
                                            
                                    /*$('#facetContainer > .btn-group').append('<div class="btn-group" style="margin:5px;">' +
                                        '<a class="btn-sm btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
                                        '<span class="orgFacetHeader">' + facetHeader + '</span>' +
                                        '</a>' +
                                        '<div class="dropdown-menu" aria-labelledby="dropdownMenuLink" id="facetSub' + i + '">' +
                                        '</div>' +
                                        '</div>');
                                    */
                                    //$('.facetMain').append('<li class="nav-item lth_solr_facet_header"><a onclick="displayFacetSub(\'facetSub' + i + '\');" class="nav-link" data-val="' + facetHeader + '" href="javascript:">' + facetHeader +  '</a></li>');
                                    //$('#facetContainer').append('<ul id="facetSub' + i + '" style="display:none;" class="nav nav-pills facetSub"></ul>');
                                }
                                $('#facetSub' + i + '> .list-group').append('<div style="white-space:nowrap;"><input class="lth_solr_facet" type="checkbox" name="' + key + '###' + facetVal + '" id="' + key + '###' + facetVal + '" value="' + key + '###' + facetVal + '" />' +
                                    '<label class="list-group-item" for="' + key + '###' + facetVal + '">' + facetVal.replace(/_/g, ' ') + ' (' + count + ')</label></div>' +
                                    '');
                                //$('#facetSub' + i).append('<a id="' + key + facetVal + '" class="dropdown-item lth_solr_facet" data-val="' + key + '###' + facetVal + '" href="javascript:">' + facetVal.replace(/_/g, ' ') + ' (' + count + ')</a>');
                                //$('#facetSub' + i).append('<div class="checkbox"><label><input type="checkbox">' + facetVal.replace(/_/g, ' ') + ' (' + count + ')</label>');
                                oldFacetHeader = facetHeader;
                            });
                        });
                        $('.list-group input').on('click', function(){
                            //$(this).parent().parent().find('.orgFacetHeader').hide();
                            //$(this).parent().parent().find('.activeFacetHeader').remove();
                            //$(this).parent().parent().find('.dropdown-toggle').append('<span class="activeFacetHeader" id="' + $(this).attr('id') + '2" data-val="' + $(this).attr('data-val') + '">' + $(this).html() + '</span>');
                            $(this).toggleClass('active');
                            listOrganisationPublications(getActiveCheckboxes(), $('#lthsolr_organisation_filter').val(),0,'');
                        });
                        /*$('.lth_solr_facet a').each(function() {
                            $(this).click(function() {
                                if($(this).hasClass('active')) {
                                    $(this).removeClass('active');
                                } else {
                                    $(this).addClass('active');
                                }
                                listOrganisationPublications(getActiveFacets(), $('#lthsolr_organisation_filter').val(),0,'');
                            });
                        });*/
                        //$('#lth_solr_totalcount').val(totalCount);
                        $('#lthSolrFacetShow').click(function(){
                            $('#facetContainer').toggle(400);
                            $(this).toggleClass('active');
                        });
                    } else {
                        /*$('.lth_solr_facet').each(function(){
                            $(this).next().text($(this).val().split('###').pop() + '(0)');
                        });*/
                        $.each( d.facet, function( key, value ) {
                            
                            $.each( value, function( key1, value1 ) {
                                //console.log(value1[0].toString() + ';' + value1[1]);
                                facetVal = value1[0].toString();
                                count = value1[1];
                                //$('#' + key + facetVal + ' a').text(facetVal.replace(/_/g, ' ') + ' (' + count + ')');
                                //$("[id='content Module']")
                                $("[id='" + key + '###' + facetVal + "']").next().text(facetVal.replace(/_/g, ' ') + ' (' + count + ')');
                            });
                        });
                    }
                } else {
                    $('#facetContainer').empty();
                }
                
                var all = 'Samtliga';
                    var among = 'bland';
                    if(sysLang=='en') {
                        all = 'All';
                        among = 'among';
                    }
                    if(!facet && !query) query = all;
                    if(facet && query) query = query + ' ' + among + ' ';
                $('#lthsolr_organisation_container > div > section').remove('h2').append('<h2 class="m-0 pb-2 border-bottom">' + tableLength + ' ' + lth_solr_messages.of + ' ' + d.numFound + '</h2>');

                var scopeArray = scope.split(',');
                var indexArray;

                $.each( d.data, function( key, aData ) {

                    var template = $('#solrPublicationTemplate').html();
                    
                    articleNumber = '';
                    authorName = '';
                    bibliographicalNote = ''.
                    documentTitle = '';
                    electronicIsbn = '';
                    electronicVersion = '';
                    
                    hostPublicationTitle = '';
                    journalTitle = '';
                    link = '';
                    numberOfPages = '';
                    openAccessPermission = '';
                    pages = '';
                    publicationDate = '';
                    publisher = '';
                    placeOfPublication = '';
                    portalUrl = '';
                    publicationStatus = '';
                    publicationType = '';
                    title = '';
                    
                    volume = '';
                    
                    //id
                    id = aData.id;
                    
                    //documentTitle
                    if(aData.documentTitle) {
                        title = aData.documentTitle; //.charAt(0).toUpperCase() + aData.documentTitle.slice(1).toLowerCase();
                    } else {
                        title = 'untitled';
                    }

                    //title = '<a href="' + path + '/' + title.replace(/[^\w\s-]/g,'').replace(/ /g,'-').toLowerCase() + '('+id+')(publication)">' + title + '</a>';
                    
                    //articleNumber
                    if(aData.articleNumber) articleNumber = ', ' + aData.articleNumber;
                    
                    //openAccessPermission
                    if(aData.openAccessPermission) openAccessPermission = aData.openAccessPermission;

                    if(electronicVersionFileURL || openAccessPermission) {
                        if(electronicVersionFileURL) {
                            electronicVersion = '<i class="fa fa-paperclip"></i>';
                        }
                        if(openAccessPermission) {
                            if(openAccessPermission==='Öppen' || openAccessPermission==='Open') {
                                electronicVersion += '<i class="fa fa-unlock"></i> Open access';
                            }
                        }
                    }
        
                    //authorName
                    if(aData.authorName) {
                        $.each( aData.authorName, function( anKey, anValue ) {
                            if(authorName) authorName += ', ';
                            authorName += anValue;
                        });
                    }
                    
                    //hostPublicationTitle
                    if(aData.hostPublicationTitle) hostPublicationTitle = '<i>' + aData.hostPublicationTitle + '</i>. ';
                    
                    //pages
                    if(aData.pages) pages = lth_solr_messages.pagesAbbreviation + ' ' + aData.pages + ' ';
                    
                    //portalUrl
                    //"portalUrl":"http://portal.research.lu.se/portal/en/publications/universal-exiles(599b6b45-7f64-4e97-a013-85afddd92bbb).html",
                    if(aData.portalUrl) {
                        portalUrl = aData.portalUrl;
                        link = path + '/' + portalUrl.split('/').pop().split('(').shift();
                        //link = portalUrl.split('/').pop().split('(').shift();
                    }
                    
                    //publicationDate
                    if(aData.publicationDateYear) publicationDate = aData.publicationDateYear;
                    if(aData.publicationDateMonth) publicationDate += '-'+aData.publicationDateMonth;
                    if(aData.publicationDateDay) publicationDate += '-'+aData.publicationDateDay;
                    if(publicationDate) publicationDate = publicationDate + ' ';
                    
                    //publicationStatus
                    if(aData.publicationStatus) publicationStatus = aData.publicationStatus;
                    
                    //publicationStatus
                    if(aData.publicationType) publicationType = aData.publicationType;
                    
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
                    
                    //###bibliographicalNote
                    if(aData.bibliographicalNote) {
                        bibliographicalNote = aData.bibliographicalNote;
                    }

                    template = template.replace('###articleNumber###', articleNumber);
                    template = template.replace('###authorName###', authorName);
                    template = template.replace('###bibliographicalNote###', bibliographicalNote);
                    template = template.replace('###id###', id);
                    template = template.replace('###hostPublicationTitle###', hostPublicationTitle);
                    template = template.replace('###journalTitle###', journalTitle);
                    template = template.replace('###link###', link);
                    template = template.replace('###numberOfPages###', numberOfPages);
                    template = template.replace('###pages###', pages);
                    template = template.replace('###publicationType###', publicationType);
                    template = template.replace('###publicationDate###', publicationDate);
                    template = template.replace('###publicationStatus###', publicationStatus);
                    template = template.replace('###publisher###', publisher);
                    template = template.replace('###placeOfPublication###', placeOfPublication);
                    template = template.replace('###title###', title);
                    template = template.replace('###volume###', volume);
                    template = template.replace('###electronicVersion###', electronicVersion);

                    $('#lthsolr_organisation_container  > div > section').append(template);                     
                    
                });
                if(d.numFound > (parseInt(tableStart) + 50)) {
                    $('#lthsolr_organisation_container > div > section').append('<div class="lthSolrMoreContainer"><button type="button" class="btn  btn-outline-primary">' + moreText + '</button></div>');
                    $('.lthSolrMoreContainer .btn').click(function(){
                        listOrganisationPublications(getActiveFacets(), $('#lthsolr_organisation_filter').val(), parseInt(tableStart) + 50, 'more');
                    });
                    
                }
            }
        }
    });
}


function latestDissertationsStudentPapers(tableStart)
{
    var sysLang = $('html').attr('lang');
    var scope = $('#lth_solr_scope').val();
    var publicationsLink = $('#lth_solr_publicationslink').val();
    var dissertationsLink = $('#lth_solr_dissertationslink').val();
    var detailPage = 'visa';
    if(sysLang==='en') detailPage = 'show';
    //var exportArray = ["firstName","lastName","title","phone","email","organisationName","homepage","roomNumber","mobile"];
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'latestDissertationsStudentPapers',
            dataSettings: {
                pageid: $('body').attr('id').replace('p',''),
                scope: scope,
                syslang: sysLang,
                tableStart: tableStart
            },
            sid : Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(jq + ';' + st + " : " + err);
        },
        beforeSend: function () {
            $('.swipe-inner').append(getSpinner(sysLang));
        },
        success: function(d) {
            $('.spinner').remove();

            if(d.data) {
             
                var path = window.location.href;
                
                var title, link, activeSwipe='', lastIndex=0, firstIndex=0;
                var i = 0;
                $.each( d.data, function( key, aData ) {
                    if(i>3) return false;
                    //if(i<3) {
                        addSwipeItem(aData, path, i, 'after', dissertationsLink, publicationsLink, detailPage);
                        
                        if(i===0) {
                            activeSwipe=' active';
                        } else {
                            activeSwipe='';
                        }
                        //$('#swipe').append('<div class="swipe-step' + activeSwipe + '"></div>')
                        i++;
                    //}
                });
                //console.log(i);
                $('.swipe-inner').prepend('<a class="swipe-control left" href="javascript:" data-slide="prev">&lsaquo;</a>');
                $('.swipe-inner').append('<a class="swipe-control right" href="javascript:" data-slide="prev">&rsaquo;</a>');
                $('.swipe-control.left').addClass('disabled');
                $('.swipe-control.left').click(function() {
                    firstIndex = $('.swipe-target').first().attr('data-index');
                    if(parseInt(firstIndex)===0) {
                        $('.swipe-control.left').addClass('disabled');
                    } else {
                        $('.swipe-target').last().remove();
                        addSwipeItem(d.data[parseInt(firstIndex)-1], path, parseInt(firstIndex)-1, 'before', dissertationsLink, publicationsLink, detailPage);                   
                        if(parseInt(firstIndex)-1===0) {
                            $('.swipe-control.left').addClass('disabled');
                        } else {
                            $('.swipe-control.left').removeClass('disabled');
                        }
                    }
                });
                $('.swipe-control.right').click(function() {
                    $('.swipe-target').first().remove();
                    lastIndex = $('.swipe-target').last().attr('data-index');
                    addSwipeItem(d.data[parseInt(lastIndex)+1], path, parseInt(lastIndex)+1, 'after', dissertationsLink, publicationsLink, detailPage);
                    firstIndex = $('.swipe-target').first().attr('data-index');
                    if(parseInt(firstIndex)===0) {
                        $('.swipe-control.left').addClass('disabled');
                    } else {
                        $('.swipe-control.left').removeClass('disabled');
                    }
                });
                $(".swipe-inner").swipe( {
                    //Generic swipe handler for all directions
                    swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
                        //$(this).text("You swiped " + direction );
                        if(direction==='left') {
                            $('.swipe-target').first().remove();
                            lastIndex = $('.swipe-target').last().attr('data-index');
                            addSwipeItem(d.data[parseInt(lastIndex)+1], path, parseInt(lastIndex)+1, 'after', dissertationsLink, publicationsLink, detailPage);
                            firstIndex = $('.swipe-target').first().attr('data-index');
                            if(parseInt(firstIndex)===0) {
                                $('.swipe-control.left').addClass('disabled');
                            } else {
                                $('.swipe-control.left').removeClass('disabled');
                            }
                        }
                        if(direction==='right') {
                            firstIndex = $('.swipe-target').first().attr('data-index');
                            if(parseInt(firstIndex)===0) {
                                $('.swipe-control.left').addClass('disabled');
                            } else {
                                $('.swipe-target').last().remove();
                                addSwipeItem(d.data[parseInt(firstIndex)-1], path, parseInt(firstIndex)-1, 'before', dissertationsLink, publicationsLink, detailPage);                   
                                if(parseInt(firstIndex)-1===0) {
                                    $('.swipe-control.left').addClass('disabled');
                                } else {
                                    $('.swipe-control.left').removeClass('disabled');
                                }
                            }
                        }
                    },
                    //Default is 75px, set to 0 for demo so any distance triggers swipe
                    threshold:0
                });
                
            }
        }
    });
}


function addSwipeItem(aData, path, i, type, dissertationsLink, publicationsLink, detailPage)
{
    var template = $('#solrSwipeTemplate').html();
    var title, link;

                        if(aData.documentTitle) {
                            //title = '<a href="index.php?id=' + detailPage + '&uuid=' + aData[0] + '&no_cache=1">' + aData[1] + '</a>';
                            title = aData.documentTitle.charAt(0).toUpperCase() + aData.documentTitle.slice(1).toLowerCase();
                        } else {
                            title = 'untitled';
                        }
                        var bgColorArray = ['copper','dark','flower','plaster','sky','stone'];
                        var rn = Math.floor(Math.random() * 5);
                        var publicationDate = ''
                        if(aData.publicationDate) publicationDate = aData.publicationDate;
                        //title = '<a href="' + path + '/' + title.replace(/[^\w\s-]/g,'').replace(/ /g,'-').toLowerCase() + '('+aData[0]+')">' + title + '</a>';
                        
                        if(aData.docType === 'publication') {
                            link = '/' + publicationsLink.replace('//', '/') + '/' + detailPage + '/' + title.toLowerCase();
                        }
                        if(aData.docType === 'studentPaper') {
                            link = '/' + dissertationsLink.replace('//', '/') + '/'  + detailPage + '/' + title.toLowerCase();
                        }
                        //.replace(/[^\w\s-]/g,'').replace(/ /g,'-').toLowerCase();

                        //template = template.replace('###id###', aData.id);
                        template = template.replace(/###i###/g, i);
                        template = template.replace('###title###', title);
                        template = template.replace('###link###', link);
                        template = template.replace('###authorName###', aData.authorName);
                        template = template.replace(/###publicationDate###/g, publicationDate);
                        template = template.replace('###organisationName###', aData.organisationName);
                        template = template.replace('###docType###', aData.docType.replace('publication','Avhandling').replace('studentPaper','Examensarbete'));
                        template = template.replace('###supervisorName###', aData.supervisorName);
                        template = template.replace(/###bgColor###/g, bgColorArray[rn]);

                        if($('.swipe-target').length === 0) {
                            $('.swipe-inner').append(template);
                        } else if(type==='after') {
                            $('.swipe-target').last().after(template);
                        } else if(type==='before') {
                            $('.swipe-target').first().before(template);
                        }
                        
}


function listOrganisationProjects(facet, query, tableStart)
{
    var sysLang = $('html').attr('lang');
    var scope = $('#lth_solr_scope').val();
    var facetChoice = $('#lth_solr_facetchoice').val();
    var inputFacet = facet;
    var facetVal, count;
    if(!facet) facet = '';
    var detailPage = 'visa';
    if(sysLang==='en') detailPage = 'show';
    //var exportArray = ["firstName","lastName","title","phone","email","organisationName","homepage","roomNumber","mobile"];
     
    if($('.lth_solr_culdesac').length === 0) {
        $('#lthsolr_projects_filter').parent().find('button').addClass('lth_solr_culdesac').click(function() {
            if($('#lthsolr_organisation_filter').val().trim().length > 2) {
                $('#facet_container .nav-link').removeClass('active');
                //listOrganisationStaff($('#facet_container > li > .active').attr('data-val'), $('#lthsolr_organisation_filter').val().trim());
            } else if(facetChoice==='firstLetter') {
                $('#facet_container .nav-link:eq(0)').addClass('active');
                //listOrganisationStaff('a','');
                $('#lthsolr_organisation_filter').val('');
            } else {
                $('#facet_container .nav-link:eq(0)').addClass('active');
                //listOrganisationStaff('','');
            }
            
        });
    }
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'listOrganisationProjects',
            dataSettings: {
                facet: facet,
                facetChoice: facetChoice,
                pageid: $('body').attr('id').replace('p',''),
                query: query,
                scope: scope,
                syslang: sysLang,
                tableStart: tableStart
            },
            sid : Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(jq + ';' + st + " : " + err);
        },
        beforeSend: function () {
            if($('.lthSolrMoreContainer').length > 0) {
                $('.lthSolrMoreContainer').replaceWith(getSpinner(sysLang));
            } else {
                $('#lthsolr_organisation_container > div > section').empty();
                $('#lthsolr_organisation_container > div > section').append(getSpinner(sysLang));
            }
        },
        success: function(d) {
            $('.spinner').remove();
            $('.lthsolr_more').remove();
            if(d.data) {
                var allText = 'Alla';
                if(sysLang=='en') {
                    allText = 'All';
                }
                if(d.facet) {
                    if($('#facet_container').length === 0) {
                        $('#lthsolr_organisation_container').before('<div class="row"><div class="col-12 border-lg-bottom"><ul id="facet_container" class="nav nav-pills"></ul></div></div>');

                        var facetNavActiveClass = '';
                        var i = 0;
                        var totalCount = 0;
                        $.each( d.facet, function( key, value ) {
                            $.each( value, function( key1, value1 ) {
                                facetNavActiveClass = '';
                                facetVal = value1[0].toString();
                                count = value1[1];
                                $('#facet_container').append('<li class="nav-item lth_solr_facet"><a class="nav-link" data-val="' + facetVal + '" href="javascript:">' + facetVal.replace(/_/g, ' ') + ' (' + count + ')</a></li>');
                            });

                            $('.lth_solr_facet a').each(function() {
                                $(this).click(function() {
                                    $('#facet_container .nav-link').removeClass('active');
                                    $(this).addClass('active');
                                    //if(facetChoice==='firstLetter') $('#lthsolr_organisation_filter').val('');
                                    $('#lthsolr_organisation_filter').val('');
                                    //listOrganisationStaff($(this).attr('data-val'), $('#lthsolr_organisation_filter').val());
                                });
                            });
                        });
                        $('#lth_solr_totalcount').val(totalCount);
                    }
                } else {
                    $('#facet_container').empty();
                }
                
                if(scope) {
                    $('#lthsolr_organisation_container > div > section').remove('h2').append('<h2 class="m-0 pb-2 border-bottom">Student papers (' + d.numFound + ')' + '</h2>');
                }
                
                var path = window.location.href + detailPage;
                
                var title, link;
                
                var supervisorLabel = "Handledare", moreText = 'Visa fler resultat', authorLabel = 'Författare';
                if(sysLang==='en') {
                    moreText = 'Show more results';
                    supervisorLabel = "Supervisor";
                    authorLabel = 'Author';
                }
                
                $.each( d.data, function( key, aData ) {
                    var template = $('#solrProjectTemplate').html();

                    if(aData.projectTitle) {
                        //title = '<a href="index.php?id=' + detailPage + '&uuid=' + aData[0] + '&no_cache=1">' + aData[1] + '</a>';
                        title = aData.projectTitle.charAt(0).toUpperCase() + aData.projectTitle.slice(1).toLowerCase();
                    } else {
                        title = 'untitled';
                    }
                    
                    //title = '<a href="' + path + '/' + title.replace(/[^\w\s-]/g,'').replace(/ /g,'-').toLowerCase() + '('+aData[0]+')">' + title + '</a>';
                    link = path + '/' + aData.portalUrl;//.replace(/[^\w\s-]/g,'').replace(/ /g,'-').toLowerCase();

                    template = template.replace('###id###', aData.id);
                    template = template.replace('###projectTitle###', title);
                    template = template.replace('###link###', link);
                    template = template.replace('###startDate###', aData.startDate);
                    template = template.replace('###endDate###', aData.endDate);

                    $('#lthsolr_organisation_container').append(template);
                });
                
                if(d.numFound > (parseInt(tableStart) + 100)) {
                    $('#lthsolr_organisation_container').append('<div class="lthSolrMoreContainer"><button type="button" class="btn  btn-outline-primary">' + moreText + '</button></div>');
                    $('.lthSolrMoreContainer .btn').click(function(){
                        listOrganisationProjects(facet, query, parseInt(tableStart) + 100);
                    });
                }
                
            }
            toggleFacets();
        }
    });
}


function listOrganisationStudentPapers(facet, query, tableStart, more)
{
    var sysLang = $('html').attr('lang');
    var scope = $('#lth_solr_scope').val();
    var facetChoice = $('#lth_solr_facetchoice').val();
    //var inputFacet = facet;
    var facetVal, facetHeader, count;
    if(!facet) facet = '';
    var detailPage = 'visa';
    var errorText = 'Skriv minst 3 tecken';
    if(sysLang==='en') {
        detailPage = 'show';
        errorText = 'Write at leat 3 characters';
    }
    //var exportArray = ["firstName","lastName","title","phone","email","organisationName","homepage","roomNumber","mobile"];
     
    if($('.lth_solr_culdesac').length === 0) {
        $('#lthSolrClear').click(function() {
            $('#lthsolr_organisation_filter').val('');
        });
        
        $('#lthSolrSearch').addClass('lth_solr_culdesac').click(function() {
            //console.log($('#lthsolr_organisation_filter').val());
            if($('#lthsolr_organisation_filter').val().trim().length > 2) {
                //console.log('???');
                //$('#facet_container .nav-link').removeClass('active');
                listOrganisationStudentPapers(getActiveFacets(), $('#lthsolr_organisation_filter').val().trim(),0);
            } else {
                alert(errorText);
            }
            
        });
    }
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'listOrganisationStudentPapers',
            dataSettings: {
                facet: facet.replace('nofacet',''),
                facetChoice: facetChoice,
                pageid: $('body').attr('id').replace('p',''),
                query: query,
                scope: scope,
                syslang: sysLang,
                tableStart: tableStart
            },
            sid : Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(jq + ';' + st + " : " + err);
        },
        beforeSend: function () {
            if(more) {
                $('.lthSolrMoreContainer').replaceWith(getSpinner(sysLang));
            } else {
                $('#lthsolr_organisation_container > div > section').empty();
                $('#lthsolr_organisation_container > div > section').append(getSpinner(sysLang));
            }
        },
        success: function(d) {
            $('.spinner').remove();
            $('.lthsolr_more').remove();
            if(d.data) {
                var allText = 'Alla';
                if(sysLang=='en') {
                    allText = 'All';
                }
                if(d.facet) {
                    if($('#facet_container').length === 0) {
                        $('#lthsolr_organisation_container').before('<div class="row "><div id="facet_container" class="col-12" style="display:none;"><ul class="nav nav-pills facetMain"></ul></div></div>');

                        //var facetNavActiveClass = '';
                        var i = 0;
                        var totalCount = 0;
                        var oldFacetHeader = '';
                        $.each( d.facet, function( key, value ) {
                            $.each( value, function( key1, value1 ) {
                                //facetNavActiveClass = '';
                                facetVal = value1[0].toString();
                                count = value1[1];
                                facetHeader = value1[2].toString();
                                if(facetHeader !== oldFacetHeader) {
                                    i++;
                                    $('.facetMain').append('<li class="nav-item lth_solr_facet_header"><a onclick="displayFacetSub(\'facetSub' + i + '\');" class="nav-link" data-val="' + facetHeader + '" href="javascript:">' + facetHeader +  '</a></li>');
                                    $('#facet_container').append('<ul id="facetSub' + i + '" style="display:none;" class="nav nav-pills facetSub"></ul>');
                                }
                                $('#facetSub' + i).append('<li id="' + key + facetVal + '" class="nav-item lth_solr_facet"><a class="nav-link nav-link-sm" data-val="' + key + '###' + facetVal + '" href="javascript:">' + facetVal.replace(/_/g, ' ') + ' (' + count + ')</a></li>');
                                oldFacetHeader = facetHeader;
                            });

                            
                        });
                        $('.lth_solr_facet a').each(function() {
                            $(this).click(function() {
                                //console.log($(this).hasClass('active'));
                                if($(this).hasClass('active')) {
                                    $(this).removeClass('active');
                                } else {
                                    $(this).addClass('active');
                                }
                                listOrganisationStudentPapers(getActiveFacets(), $('#lthsolr_organisation_filter').val(),0,'');
                            });
                        });
                        $('#lth_solr_totalcount').val(totalCount);
                        $('#lthSolrFacetShow').click(function(){
                            $('#facet_container').toggle(400);
                        });
                    } else {
                        //var dataValTmp = '';
                        $('.lth_solr_facet a').each(function(){
                            //dataValTmp = ;
                            $(this).text($(this).attr('data-val').split('###').pop() + '(0)');
                        });
                        $.each( d.facet, function( key, value ) {
                            $.each( value, function( key1, value1 ) {
                                //facetNavActiveClass = '';
                                facetVal = value1[0].toString();
                                count = value1[1];
                                //console.log(key + '###' + facetVal + '(' + count + ')');
                                $('#' + key + facetVal + ' a').text(facetVal.replace(/_/g, ' ') + ' (' + count + ')');
                            });
                        });
                    }
                } else {
                    $('#facet_container').empty();
                }
                
                if(scope && !more) {
                    $('#lthsolr_organisation_container > div > section').remove('h2').append('<h2 class="m-0 pb-2 border-bottom">Student papers (' + d.numFound + ')' + '</h2>');
                }
                
                var path = window.location.href + detailPage;
                
                var title, link;
                
                var supervisorLabel = "Handledare", moreText = 'Visa fler resultat', authorLabel = 'Författare';
                if(sysLang==='en') {
                    moreText = 'Show more results';
                    supervisorLabel = "Supervisor";
                    authorLabel = 'Author';
                }
                
                $.each( d.data, function( key, aData ) {
                    var template = $('#solrStudentPapersTemplate').html();

                    if(aData.documentTitle) {
                        //title = '<a href="index.php?id=' + detailPage + '&uuid=' + aData[0] + '&no_cache=1">' + aData[1] + '</a>';
                        title = aData.documentTitle.charAt(0).toUpperCase() + aData.documentTitle.slice(1).toLowerCase();
                    } else {
                        title = 'untitled';
                    }
                    
                    //title = '<a href="' + path + '/' + title.replace(/[^\w\s-]/g,'').replace(/ /g,'-').toLowerCase() + '('+aData[0]+')">' + title + '</a>';
                    link = path + '/' + title.toLowerCase();//.replace(/[^\w\s-]/g,'').replace(/ /g,'-').toLowerCase();

                    template = template.replace('###id###', aData.id);
                    template = template.replace('###title###', title);
                    template = template.replace('###link###', link);
                    template = template.replace('###authorName###', '<b>' + authorLabel + '</b>: ' + aData.authorName);
                    template = template.replace(/###publicationDateYear###/g, aData.publicationDateYear);
                    template = template.replace('###organisationName###', aData.organisationName);
                    template = template.replace('###supervisorName###', '<b>' + supervisorLabel + '</b>: ' + aData.supervisorName);
                    template = template.replace('###bibtex###', aData.bibtex);

                    $('#lthsolr_organisation_container > div > section').append(template);
                });
                
                if(d.numFound > (parseInt(tableStart) + 100)) {
                    $('#lthsolr_organisation_container > div > section').append('<div class="lthSolrMoreContainer"><button type="button" class="btn  btn-outline-primary">' + moreText + '</button></div>');
                    $('.lthSolrMoreContainer .btn').click(function(){
                        listOrganisationStudentPapers(getActiveFacets(), $('#lthsolr_organisation_filter').val(), parseInt(tableStart) + 100, 'more');
                    });
                    
                }
                
            }
        }
    });
}


function displayFacetSub(facetSubId)
{
    //$('.facetSub').not('#' + facetSubId).hide();
    $('#' + facetSubId).toggle(400);
}


function postIt(e, id, link)
{
    e.preventDefault();
    $('#lth_solr_id').val(id);
    //
    window.history.pushState({path:link},'',link);
    $('#postIt').submit();
}


function listOrganisationStaff(facet, query, tableStart, more)
{
    var extraPeople = $('#lth_solr_extrapeople').val();
    var facetChoice = $('#lth_solr_facetchoice').val();
    var sysLang = $('html').attr('lang');
    var scope = $('#lth_solr_scope').val();
    var vroles = $('#lth_solr_vroles').val();
    var action = $('#lth_solr_action').val();
    var moreText = 'Visa fler resultat';
    var errorText = 'Skriv minst 3 tecken';
    if(sysLang==='en') {
        errorText = 'Write at leat 3 characters';
        moreText = 'Show more results';
    }
    
    if($('#lth_solr_query').val().trim().length > 2) {
        query = $('#lth_solr_query').val().trim();
        $('#lthsolr_organisation_filter').val(query);
        $('#lth_solr_query').val('');
    } /*else if($('#lthsolr_organisation_filter').val().trim().length > 2) {
        query = $('#lth_solr_query').val();        
    } else {
        query = '';
    }*/
    
    var facetVal, count;
    if(!facet) facet = '';
    var detailPage = 'visa';
    if(sysLang==='en') detailPage = 'show';
    //var exportArray = ["firstName","lastName","title","phone","email","organisationName","homepage","roomNumber","mobile"];
     
    if($('.lth_solr_culdesac').length === 0) {
        
        $('#lthSolrClear').click(function() {
            $('#lthsolr_organisation_filter').val('');
            $('#lthSolrFacetShow').removeClass('active');
            $('#facet_container_1 .nav-link, #facet_container_2 .nav-link').not($(this)).removeClass('active');
            listOrganisationStaff(getActiveFacets('#facet_container_2'), $('#lthsolr_organisation_filter').val(),0,'');
        });
        $('#lthSolrFacetShow').addClass('active');
        $('#lthSolrSearch').addClass('lth_solr_culdesac').click(function() {
            if($('#lthsolr_organisation_filter').val().trim().length > 2) {
                $('#facet_container_1 .nav-link').removeClass('active');
                listOrganisationStaff(getActiveFacets(), $('#lthsolr_organisation_filter').val().trim(),0,'');
            } else {
                alert(errorText);
            }
            if(facetChoice==='firstLetter') {
                $('#facet_container .nav-link:eq(0)').addClass('active');
                //listOrganisationStaff('a','',0,'');
                //$('#lthsolr_organisation_filter').val('');
            } else {
                $('#facet_container .nav-link:eq(0)').addClass('active');
                //listOrganisationStaff('','',0,'');
            }
        });
    }
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: action,
            dataSettings: {
                extraPeople: extraPeople,
                facet: facet,
                facetChoice: facetChoice,
                pageid: $('body').attr('id').replace('p',''),
                query: query,
                scope: scope,
                syslang: sysLang,
                tableStart: tableStart,
                vroles: vroles,
            },
            sid : Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            if(more) {
                $('.lthSolrMoreContainer').replaceWith(getSpinner(sysLang));
            } else {
                $('#lthsolr_organisation_container > div > section').empty();
                $('#lthsolr_organisation_container > div > section').append(getSpinner(sysLang));
            }
        },
        success: function(d) {
            $('.spinner').remove();
            
            //var staffDetailPage = 'visa';
            var allText = 'Alla';
            //var restText = 'Övriga';
            if(sysLang=='en') {
                //staffDetailPage = 'show';
                allText = 'All';
                //var restText = 'Other';
            }

            if(d.data) {
                //
                if(d.mailDelivery && $('.lth_solr_husmap').length > 0 && facetChoice!=='firstLetter') {
                    $('.lth_solr_husmap').show(200);
                    //$('.lth_solr_husmap #'+d.mailDelivery).attr('src','/typo3conf/ext/lth_solr/res/hus/' + d.mailDelivery+'g.png');
                    $('.lth_solr_husmap #'+d.mailDelivery).attr('src','/typo3conf/ext/lth_solr/res/reddot.png').show(200);
                }
                                
                if(d.facet) {
                    if($('#facet_container_1').length === 0) {
                        $('#lthsolr_organisation_container').before('<div class="row lthSolrFirstLetter"><div class="col-12"><ul id="facet_container_1" class="nav nav-pills"></ul></div></div>');
                        $('#lthsolr_organisation_container').before('<div class="row lthSolrOtherFacets" style="display:none;"><div class="col-12"><ul id="facet_container_2" class="nav nav-pills"></ul></div></div>');
                        //$('#facet_container').empty();
                    
                        /*if(facetChoice!=='firstLetter') {
                            $('#facet_container').append('<li class="nav-item lth_solr_facet"><a class="nav-link active" data-val="" href="#">' + allText + ' (' + d.numFound + ')</a></li>');
                        }*/

                        var facetNavActiveClass = '';
                        var i = 0;
                        var totalCount = 0;
                        $.each( d.facet, function( key, value ) {
                            $.each( value, function( key1, value1 ) {
                                facetNavActiveClass = '';
                                facetVal = value1[0].toString();
                                count = value1[1];
                                if(key==='firstLetterExact') {
                                    if(i===0 && !query) facetNavActiveClass = ' active';
                                    $('#facet_container_1').append('<li id="' + key + facetVal + '" class="nav-item lth_solr_facet"><a class="nav-link' + facetNavActiveClass + '" data-val="' + key + '###' + facetVal + '" href="javascript:">' + facetVal.toUpperCase() + '</a></li>');
                                    totalCount = totalCount + count;
                                    i++;
                                } else {
                                    $('#facet_container_2').append('<li id="' + key + facetVal + '" class="nav-item lth_solr_facet"><a class="nav-link" data-val="' + key + '###' + facetVal + '" href="javascript:">' + facetVal.replace(/_/g, ' ') + ' (' + count + ')</a></li>');
                                }
                            });
                        });
                        $('#facet_container_1 .lth_solr_facet a').each(function() {
                            $(this).click(function() {
                                $('#facet_container_1 .nav-link, #facet_container_2 .nav-link').not($(this)).removeClass('active');
                                if($(this).hasClass('active')) {
                                    $(this).removeClass('active');

                                } else {
                                    $(this).addClass('active');

                                }
                                listOrganisationStaff(getActiveFacets('#facet_container_1'), $('#lthsolr_organisation_filter').val(),0,'');
                            });
                        });
                        $('#facet_container_2 .lth_solr_facet a').each(function() {
                            $(this).click(function() {
                                $('#facet_container_1 .nav-link, #facet_container_2 .nav-link').not($(this)).removeClass('active');
                                if($(this).hasClass('active')) {
                                    $(this).removeClass('active');
                                } else {
                                    $(this).addClass('active');
                                }
                                listOrganisationStaff(getActiveFacets('#facet_container_2'), $('#lthsolr_organisation_filter').val(),0,'');
                            });
                        });
                        $('#lth_solr_totalcount').val(totalCount);
                        $('#lthSolrFacetShow').click(function(){
                            $('.lthSolrFirstLetter').toggle(200);
                            $('.lthSolrOtherFacets').toggle(200);
                            if($(this).hasClass('active')) {
                                $(this).removeClass('active');
                            } else {
                                $(this).addClass('active')
                            }
                        });
                    }
                } else {
                    $('#facet_container_1, #facet_container_2').empty();
                }
                
                if(scope && (query || facetChoice==='firstLetter') && !more) {
                    /*if(facetChoice==='firstLetter' && !query && !facet && !more) {
                        query = 'A';
                    }*/
                    if(facet) {
                        //alert(JSON.parse(facet));
                        facet = JSON.parse(facet).toString();
                    } else {
                        facet = '';
                    }
                    if(query) {
                        query = '"' + query + '"';
                    } else {
                        query = '';
                    }
                    var all = 'Samtliga';
                    var among = 'bland';
                    if(sysLang=='en') {
                        all = 'All';
                        among = 'among';
                    }
                    if(!facet && !query) query = all;
                    if(facet && query) query = query + ' ' + among + ' ';
                    $('#lthsolr_organisation_container > div > section').remove('h2').append('<h2 class="m-0 pb-2 border-bottom">' + titleCase(query + facet.split('###').pop().replace(/_/g,' ')) + ' (' + d.numFound + ' ' + lth_solr_messages.of + ' ' + $('#lth_solr_totalcount').val() + ')' + '</h2>');
                } else if(scope && !more) {
                    $('#lthsolr_organisation_container > div > section').remove('h2').append('<h2 class="m-0 pb-2 border-bottom">' + d.organisationTitle + ' (' + d.numFound + ')' + '</h2>');
                }
                var scopeArray = scope.split(',');
                if(d.singleScope) scopeArray = d.singleScope.split(',');
                var indexArray;

                $.each( d.data, function( key, aData ) {

                    var template = $('#solrStaffTemplate').html();
                    
                    var phone = '', email = '', image = '', guid = '', portalUrl = '', uuid = '', title = '', displayName = '', link = '', organisation = '', organisationPrimary = '';
                    var heritage2;
                    if(aData.guid) guid = aData.guid[0];
                   
                    if(aData.uuid) uuid = aData.uuid;
                    if(!uuid && guid) {
                        uuid = guid;
                    }
                    
                    template = template.replace('###id###', uuid);

                    if(aData.name) displayName = aData.name;
                    if(aData.email) email = aData.email;
                    if(aData.portalUrl) portalUrl = aData.portalUrl;
                    //if(aData.primaryVroleTitle) primaryVroleTitle = titleCase(aData.primaryVroleTitle);
                    indexArray = new Array();
                    if(aData.heritage2 && scope && aData.organisationId) {
                        i=0;
                        $.each( JSON.parse(aData.heritage2), function( hKey, hData ) {
                            hData = 's' + hKey + ',' + hData;
                            $.each( scopeArray, function( sKey, sData) {
                                if( (hData.indexOf(sData.split('__').pop()) > 0) && (aData.organisationHideOnWeb[i] !== '1')) {
                                    if(aData.organisationName[i]) {
                                        if(organisation && aData.organisationName[i] === aData.organisationName[(i)-1]) {
                                            organisation += ', ';
                                        } else {
                                            if(organisation) {
                                                organisation += '<br />';
                                            }
                                            organisation += '<strong>' + aData.organisationName[i] + '</strong> - ';
                                        }
                                    }
                                    if(aData.title[i]) {
                                        organisation += titleCase(aData.title[i]);
                                    }
                                    if(aData.organisationLeaveOfAbsence[i]==='1') {
                                        organisation += ' (' + lth_solr_messages.organisationLeaveOfAbsence + ')';
                                    }
                                    if(aData.phone[i] && aData.phone[i] !== 'NULL') {
                                        if(phone) phone += '<br />';
                                        phone += formatPhone(aData.phone[i]);
                                    } 
                                    if(aData.mobile[i] && aData.mobile[i] !== 'NULL') {
                                        if(phone) phone += '<br />';
                                        phone += aData.mobile[i];
                                    }
                                    return false;
                                }
                            });
                            i++;
                        });
                        //console.log(aData.heritage2);
                        //console.log(scope);
                    } else {
                        organisation += '<strong>' + aData.organisationName[0] + '</strong> - ';
                        organisation += titleCase(aData.primaryVroleTitle);
                    }
                    if(!phone && aData.primaryVrolePhone && aData.primaryVrolePhone !== 'NULL') {
                        phone = formatPhone(aData.primaryVrolePhone);
                    }
                    
                    if(organisation)organisationPrimary += '<br />';
                    template = template.replace(/###email###/g, email);
                    template = template.replace('###organisation###', organisation);
                    template = template.replace(/###phone###/g, phone);
                    //template = template.replace(/###title###/g, primaryVroleTitle);
                    
                    //template = template.replace(/###displayName###/g, '<a href="'+homepage+'">' + displayName + '</a>');
                    template = template.replace(/###displayName###/g, displayName);
                    
                    if(aData.homepage) {
                        link = aData.homepage;
                    } else {
                        //link = '/' + portalUrl;
                        link = detailPage + '/' + displayName.replace(' ','-') + '(' + uuid + ')/';
                        //link = displayName.replace(' ','-');
                    }
                    template = template.replace('###link###', link);

                    //if(showPictures==='yes') {
                        image = '/typo3conf/ext/lth_solr/res/noimage.gif';
                        //image = "/typo3conf/ext/lth_solr/res/dummy/" + (Math.floor(Math.random() * 10) + 1) + ".jpg";
                        if(aData.image) {
                            //image = '<img id="'+ii+'" src="' + aData.image + '" />';
                            image = aData.image;
                        }
                    //}
                    template = template.replace('###image###', image);
                    
                    /*
                    template = template.replace('###roomNumber###', roomNumber);*/
                    if(organisation) $('#lthsolr_organisation_container  > div > section').append(template);

                });
                if(d.numFound > (parseInt(tableStart) + 100)) {
                    $('#lthsolr_organisation_container > div > section').append('<div class="lthSolrMoreContainer"><button type="button" class="btn  btn-outline-primary">' + moreText + '</button></div>');
                    $('.lthSolrMoreContainer .btn').click(function(){
                        listOrganisationStaff(getActiveFacets(), $('#lthsolr_organisation_filter').val(), parseInt(tableStart) + 100, 'more');
                    });
                    
                }
                
            }            
        }
    });
}


function listOrganisationRoles(query)
{
    var sysLang = $('html').attr('lang');
    var scope = $('#lth_solr_scope').val();
    var vroles = $('#lth_solr_vroles').val();
    var detailPage = 'visa';
    if(sysLang==='en') detailPage = 'show';
    //var exportArray = ["firstName","lastName","title","phone","email","organisationName","homepage","roomNumber","mobile"];

    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: 'listOrganisationRoles',
            dataSettings: {
                pageid: $('body').attr('id').replace('p',''),
                scope: scope,
                vroles: vroles,
                syslang: sysLang,
                query: query,
            },
            sid : Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            $('#lthsolr_organisation_container > div > section').empty();
            $('#lthsolr_organisation_container > div > section').append(getSpinner(sysLang));
        },
        success: function(d) {
            $('.spinner').remove();
                
            if(d.data) {
                if($('.lth_solr_culdesac').length === 0) {
                    $('#lthsolr_organisation_filter').addClass('lth_solr_culdesac').keyup(function() {
                        listOrganisationRoles($(this).val().trim());
                    });
                }
            
                $('#lthsolr_organisation_container > div > section').append('<h2 class="m-0 pb-2 border-bottom">' + d.numFound + ' träffar</h2>');
                $.each( d.data, function( key, aData ) {
                    var template = $('#solrStaffTemplate').html();

                    var phone = '', email = '', image = '', guid = '', uuid = '', title = '', displayName = '', link = '', organisation = '';
                    if(aData.guid) guid = aData.guid[0];
                   
                    if(aData.uuid) uuid = aData.uuid;
                    if(!uuid && guid) {
                        uuid = guid;
                    }
                    
                    template = template.replace('###id###', uuid);

                    if(aData.firstName && aData.lastName) displayName = aData.firstName + ' ' + aData.lastName;

                    if(aData.email) email = aData.email;
                    if(aData.organisationName) organisation = '<strong>' + aData.organisationName + '</strong> - ';
                    if(aData.phone && aData.phone !== 'NULL') phone = formatPhone(aData.phone);
                    if(aData.title) organisation += titleCase(aData.title);
                    
                    template = template.replace(/###email###/g, email);
                    template = template.replace('###organisation###', organisation);
                    template = template.replace(/###phone###/g, phone);
                    template = template.replace(/###title###/g, title);
                    

                    //template = template.replace(/###displayName###/g, '<a href="'+homepage+'">' + displayName + '</a>');
                    template = template.replace(/###displayName###/g, displayName);
                    
                    if(aData.homepage) {
                        link = aData.homepage;
                    } else {
                        link = detailPage + '/' + displayName.replace(' ','-') + '(' + uuid + ')/';
                    }
                    template = template.replace('###link###', link);

                    //if(showPictures==='yes') {
                        image = '/typo3conf/ext/lth_solr/res/noimage.gif';
                        //image = "/typo3conf/ext/lth_solr/res/dummy/" + (Math.floor(Math.random() * 10) + 1) + ".jpg";
                        if(aData.image) {
                            //image = '<img id="'+ii+'" src="' + aData.image + '" />';
                            image = aData.image;
                        }
                    //}
                    template = template.replace('###image###', image);
                    
                    /*
                    template = template.replace('###roomNumber###', roomNumber);*/
                    $('#lthsolr_organisation_container > div > section').append(template);
                    //console.log(lastHeight+';'+lastId);
                });
            }
            
            toggleFacets();
        }
    });
}


function showProjectNovo()
{
    var authorName, id, documentId, documentTitle, curtailed, endDate, journalTitle, managingOrganisationName, organisationId, organisationName, 
            organisationType, participants='',participants,projectDescription='',projectTitle,projectStatus,startDate,
            participantOrganisationId, participantOrganisationName, pages, participantRole, peopleLink, homepage, publicationDateYear, 
            publicationType, publisher, participantOrganisationType, allSame = false, homepage='';
    var sysLang = $('html').attr('lang');
    
    $.ajax({
        type : "POST",
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'showProjectNovo',
            dataSettings: {
                pageid : $('body').attr('id'),
                organisation : $('#lth_solr_organisation').val(),
                scope : $('#lth_solr_scope').val(),
                sysLang : sysLang,
            },
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: "json",
        beforeSend: function () {
            $('#lthsolr_show_procect_container').append(getSpinner(sysLang));
        },
        success: function(d) {
            var projectDetailPage = 'visa';
            if(sysLang=='en') {
                projectDetailPage = 'show';
            }
            $('.spinner').remove();
            //Staff
            if(d.projectData) {                       
                $.each( d.projectData, function( key, aData ) {
                    var template = $('#solrProjectTemplate').html();

                id = aData.id;
                curtailed = aData.curtailed;
                endDate = aData.endDate.substr(0,10);
                managingOrganisationName = aData.managingOrganisationName;
                organisationId = aData.organisationId;
                organisationName = aData.organisationName;
                organisationType = aData.organisationType;
                
                if(aData.participantName) {
                    participantId = aData.participantId;
                    participantName = aData.participantName;
                    participantOrganisationId = aData.participantOrganisationId;
                    participantOrganisationName = aData.participantOrganisationName;
                    participantOrganisationType = aData.participantOrganisationType;
                    participantRole = aData.participantRole;
                    //var participantIdArray = participantId.split(',');
                    //var participantNameArray = participantName.split(',');
                    $.each(aData.participantName, function( partKey, partData ) {
                    //for (var j = 0; j < participantNameArray.length; j++) {
                        if(partData) {
                            if(peopleLink) {
                                homepage = peopleLink + '/' + projectDetailPage + '/' + 
                                        partData.trim().replace(' ','-') + '('+partData.trim()+')';
                            } else {
                                homepage = window.location.href.split(projectDetailPage).shift() + projectDetailPage + '/' + 
                                       partData.trim().replace(' ','-') + '('+partData.trim()+')';
                            }
                        }
                        participants += '<li><a href="' + homepage + '">' + partData.trim() + '</a></li>'
                    //}
                    });
                    
                    /*participants = '<div class="card"><div class="card-header" id="headingParticipants"><h5 class="mb-0">'+
                    '<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseParticipants" aria-expanded="true" aria-controls="collapseParticipants">'+
                        'Participants'+
                    '</button></h5></div>'+
                    '<div id="collapseParticipants" class="panel-collapse collapse show in" aria-labelledby="headingParticipants" data-parent="#lthSolrAccordion">'+
                    '<div class="card-body"><ul class="list">' + participants + '</ul></div></div></div>';*/
                }
                
                if(aData.projectDescription) {
                    $.each(aData.projectDescription, function( descKey, descData ) {
                        if(descData && descData != 'false') {
                            projectDescription = descData;
                            /*projectDescription += '<div class="card"><div class="card-header" id="headingDescription"><h5 class="mb-0">'+
                            '<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseDescription" aria-expanded="true" aria-controls="collapseDescription">'+
                                'Description'+
                            '</button></h5></div>'+
                            '<div id="collapseDescription" class="collapse show in" aria-labelledby="headingDescription" data-parent="#lthSolrAccordion">'+
                            '<div class="card-body">'+ descData + '</div></div></div>';*/
                            return false;
                        }
                    });
                    //projectDescription = aData.projectDescription;
                    
                }
                
                //projectDescriptionType = aData.projectDescriptionType;
                projectStatus = aData.projectStatus;
                projectTitle = aData.projectTitle;
                startDate = aData.startDate.substr(0,10);
                    
                template = template.replace('###endDate###', endDate.substr(0,12));
                //template = template.replace('###managingOrganisationName###', managingOrganisationName);
                template = template.replace('###organisationId###', organisationId);
                template = template.replace('###participants###', participants);
                template = template.replace('###projectDescription###', projectDescription);
                //template = template.replace('###projectDescriptionType###', projectDescriptionType);
                template = template.replace('###projectStatus###', projectStatus);
                template = template.replace('###startDate###', startDate.substr(0,12));

                $('#lthsolr_project_container').html(template);
                
                if(!projectDescription) {
                    $('.more-content').parent().remove();
                }
                    
                $('#page_title h1, article h1').text(projectTitle);
                });
            } 

            //Publications
            var i = 0;
            if(d.publicationsData) {
                $('#lthSolrLatestPublications h3').text(lth_solr_messages.latestPublications);
                $('#lthSolrAllPublications h3').text(lth_solr_messages.allPublications);
                $('#lthSolrLatestPublications p, #lthSolrAllPublications p').text(lth_solr_messages.fromLucris);
                $('.lthSolrShowAllPublications').text(lth_solr_messages.showAllPublications);
                $('.lthSolrShowLatestPublications').text(lth_solr_messages.showLatestPublications);
                
                $.each( d.publicationsData, function( key, aData ) {
                    authorName = '';
                    documentTitle = '';
                    documentId = '';
                    pages = '';
                    publicationDateYear = '';
                    publicationType = '';
                    journalTitle = '';
                    publisher = '';
                    
                    documentId = aData.id;
                    
                    if(aData.authorName) {
                        authorName = aData.authorName;
                    }
                    if(aData.documentTitle) {
                        documentTitle = aData.documentTitle;//'<h4 class="h6"><a href="' + encodeURIComponent(title.replace(/ /g,'-')) + '(' + id + ')">' + title + '</a>';
                    } else {
                        documentTitle = 'untitled';
                    }
                    if(aData.journalTitle) {
                        if(sysLang=='en') {
                            journalTitle = 'In: ' + aData.journalTitle;
                        } else {
                            journalTitle = 'I: ' + aData.journalTitle;
                        }
                    }
                    if(aData.journalTitle && aData.journalNumber) journalTitle += ' ' + aData.journalNumber;                    
                    if(aData.pages) {
                        if(sysLang=='en') {
                            pages = 'p. ' + aData.pages;
                        } else {
                            pages = 's. ' + aData.pages;
                        }
                    }
                    if(aData.publicationDateYear) {
                        publicationDateYear = aData.publicationDateYear;
                    }
                    if(aData.publicationType) {
                        publicationType = aData.publicationType;
                    }
                    if(aData.publisher) {
                        publisher = aData.publisher;
                    }

                    
                    if(i < 3) {
                        $('#lthSolrLatestPublications').append('<h4 class="h6">' + publicationDateYear + '</h4>');
                        $('#lthSolrLatestPublications').append('<p><a href="#">' + documentTitle + '</a><br/>' + authorName);
                        $('#lthSolrLatestPublications').append('<br/>(' + publicationDateYear + ') ' + publisher + '</p>');
                        
                    } else {
                        $('#lthSolrAllPublications').append('<p><h4 class="h6">'+publicationType+'</h4>');
                        $('#lthSolrAllPublications').append('<a href="#">' + documentTitle + '</a><br/>' + authorName);
                        $('#lthSolrAllPublications').append('<br/>(' + publicationDateYear + ') ' + publisher + '</p>');
                    }

                    i++;
                });
                
                $('.expand-closed').click(function() {
                    $('.expand-content-body, .expand-open').show(200);
                    $(this).toggle();
                });
                $('.expand-open').click(function() {
                    $('.expand-content-body').hide(200);
                    $('.expand-closed').toggle();
                    $(this).toggle();
                });
                //$('#my-accordion').collapse({ parent: true, toggle: true }); 
            } else {
                //$('#lthSolrLatestPublications').parent().parent().parent().hide();
            }
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
}


function showStaffNovo()
{
    var authorName, displayName, documentId, documentTitle, email, image, journalTitle, mailDelivery, organisationHideOnWeb, organisationStreet, 
            organisationPostalAddress, organisation='', organisation2='',
            organisationDescription, mobile, pages, phone, portalUrl, profileInformationJson, profileInformation, publicationDateYear, 
            publicationType, publisher, roomNumber, allSame = false;
    var sysLang = $('html').attr('lang');
    var publicationDetailPage = $('#lth_solr_publicationdetailpage').val();

    $.ajax({
        type : "POST",
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'showStaffNovo',
            dataSettings: {
                pageid : $('body').attr('id'),
                organisation : $('#lth_solr_organisation').val(),
                scope : $('#lth_solr_scope').val(),
                sysLang : sysLang,
            },
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: "json",
        beforeSend: function () {
            $('#lthsolr_show_staff_container').append(getSpinner(sysLang));
        },
        success: function(d) {
            $('.spinner').remove();
            //Staff
            if(d.staffData) {                       
                $.each( d.staffData, function( key, aData ) {
                    var template = $('#solrStaffTemplate').html();

                    if(aData.firstName && aData.lastName) displayName = aData.firstName + ' ' + aData.lastName;
                    
                    if(aData.phoneSame && aData.mobileSame && aData.roomNumberSame && aData.mailDeliverySame && aData.organisationStreetSame && aData.organisationPostalAddressSame) {
                        allSame = true;
                    }
                    
                    if(aData.organisationDescription) {
                        organisationDescription = '<p>' + aData.organisationDescription + '</p>';
                    }
                    
                    if(aData.email) {
                        email = '<strong>E-post:</strong> <a href="mailto: ' + aData.email + '">' + aData.email + '</a>';
                    }

                    for (var i=0; i<aData.organisationId.length; i++) {
                        if(aData.organisationHideOnWeb[i] === '0') {
                        //if(aData.title) {
                            if(aData.title[i]) {
                                if(organisation && allSame) organisation += '<br />';
                                organisation += '<strong>' + titleCase(aData.title[i]) + '</strong>';
                                if(aData.title[i]) organisation += ' vid ';
                            }
                            
                            
                        //}
                        
                        //if(aData.organisationName) {
                            if(aData.organisationName[i]) organisation += titleCase(aData.organisationName[i]);
                        //}
                            if(aData.organisationLeaveOfAbsence[i]==1) organisation += ' (' + lth_solr_messages.organisationLeaveOfAbsence + ')';
                        
                        //if(aData.phone) {
                            if(aData.phone[i] && aData.phone[i] !== 'NULL' && allSame) {
                                phone = '<br/><strong>Telefon:</strong> <a href="tel:'+aData.phone[i]+'">' + aData.phone[i].replace('+4646222', '+46 46 222 ').replace(/(.{2}$)/, ' $1')+ '</a>';
                            } else if(aData.phone[i] && aData.phone[i] !== 'NULL') {
                                organisation += '<br/><strong>Telefon:</strong> <a href="tel:'+aData.phone[i]+'">' + aData.phone[i].replace('+4646222', '+46 46 222 ').replace(/(.{2}$)/, ' $1')+ '</a>';
                            }
                        //}
                        
                        //if(aData.mobile) {
                            if(aData.mobile[i] && aData.mobile[i] !== 'NULL' && allSame) {
                                mobile = '+46 ' + aData.mobile[i].replace(/ /g, '').replace('+46','').replace(/(\d{2})(\d{3})(\d{2})(\d{2})/, "$1 $2 $3 $4");
                                mobile = '<br/><strong>Mobiltelefon:</strong> <a href="tel:'+mobile+'">' + mobile + '</a>';
                            } else if(aData.mobile[i] && aData.mobile[i] !== 'NULL') {
                                mobile = '+46 ' + aData.mobile[i].replace(/ /g, '').replace('+46','').replace(/(\d{2})(\d{3})(\d{2})(\d{2})/, "$1 $2 $3 $4");
                                organisation += '<br/><strong>Mobiltelefon:</strong> <a href="tel:'+mobile+'">' + mobile + '</a>';
                            }
                        //}
                        
                        //if(aData.roomNumber) {
                            if(aData.roomNumber[i] && aData.roomNumber[i] !== 'NULL' && allSame) {
                                roomNumber = '<br/><strong>Rumsnummer:</strong> ' + aData.roomNumber[i];
                            } else if(aData.roomNumber[i] && aData.roomNumber[i] !== 'NULL') {
                                organisation += '<br/><strong>Rumsnummer:</strong> ' + aData.roomNumber[i];
                            }
                        //}
                                                
                        //if(aData.mailDelivery) {
                            if(aData.mailDelivery[i] && aData.mailDelivery[i] !== 'NULL' && allSame) {
                                mailDelivery = '<br/><strong>Hämtställe:</strong> ' + aData.mailDelivery[i];
                                if($('.lth_solr_husmap').length > 0) {
                                    $('.lth_solr_husmap').show(200);
                                    $('.lth_solr_husmap #'+aData.mailDelivery[i]).attr('src','/typo3conf/ext/lth_solr/res/reddot.png').show(200);
                                }
                            } else if(aData.mailDelivery[i] && aData.mailDelivery[i] !== 'NULL') {
                                organisation += '<br/><strong>Hämtställe:</strong> ' + aData.mailDelivery[i];
                                if($('.lth_solr_husmap').length > 0) {
                                    $('.lth_solr_husmap').show(200);
                                    $('.lth_solr_husmap #'+aData.mailDelivery[i]).attr('src','/typo3conf/ext/lth_solr/res/reddot.png').show(200);
                                }
                            }
                        //}
                                               
                        //if(aData.organisationStreet) {
                            if(aData.organisationStreet[i] && allSame) {
                                organisationStreet = '<br/><strong>Adress:</strong> ' + aData.organisationStreet[i];
                            } else if(aData.organisationStreet[i]) {
                                organisation += '<br/><strong>Adress:</strong> ' + aData.organisationStreet[i];
                            }
                        //}
                                                
                        //if(aData.organisationPostalAddress) {
                            if(aData.organisationPostalAddress[i] && allSame) {
                                organisationPostalAddress = '<br/><strong>Postadress:</strong> ' + aData.organisationPostalAddress[i].toString().split('$').join(', ');
                            } else if(aData.organisationPostalAddress[i]) {
                                organisation += '<br/><strong>Postadress:</strong> ' + aData.organisationPostalAddress[i].toString().split('$').join(', ');
                            }
                        //}
                        
                            if(!allSame) organisation = '<p>' + organisation + '</p>';
                        
                        }
                        //template = template.replace('###visitingAddress###', ostreet + ' ' + ocity + addBreak(ophone));
                        //template = template.replace('###postalAddress###', addBreak(organisationPostalAddress));
                    }
                    
                    image = '/typo3conf/ext/lth_solr/res/noimage.gif';
                    //image = "/typo3conf/ext/lth_solr/res/dummy/" + (Math.floor(Math.random() * 10) + 1) + ".jpg";
                    if(aData.image) {
                        image = aData.image;
                    }
                    profileInformation = '';
                    if(aData.profileInformation) {
                        profileInformationJson = JSON.parse(aData.profileInformation);
                        Object.keys(profileInformationJson).forEach(function(key) {
                            profileInformation += '<h2>' + key + '</h2>' + profileInformationJson[key];
                        });
                    } 
                    if(allSame) organisation = '<p>' + organisation + '</p>';
                    if(allSame && organisation2) { 
                        organisation2 = '<p>' + phone + mobile + roomNumber + mailDelivery + organisationStreet + organisationPostalAddress + '</p>';
                    }
                    if(organisation2) organisation2 = organisation2.replace('<br/>','');
                    template = template.replace(/###displayName###/g, displayName);
                    template = template.replace('###email###', email);
                    template = template.replace('###image###', image);
                    template = template.replace('###organisationDescription###', organisationDescription);
                    template = template.replace('###organisation###', organisation + organisation2);
                    template = template.replace('###profileInformation###', profileInformation);
                    
                    $('#lthsolr_show_staff_container').append(template);
                });
            } 

            //Publications
            var i = 0;
            if(d.publicationsData.length > 0) {
                var show = 'visa';
                if(sysLang==='en') show = 'show';
                $('#lthSolrLatestPublications h3').text(lth_solr_messages.latestPublications);
                $('#lthSolrAllPublications h3').text(lth_solr_messages.allPublications);
                $('#lthSolrLatestPublications p, #lthSolrAllPublications p').text(lth_solr_messages.fromLucris);
                $('.lthSolrShowAllPublications').text(lth_solr_messages.showAllPublications);
                $('.lthSolrShowLatestPublications').text(lth_solr_messages.showLatestPublications);
                
                $.each( d.publicationsData, function( key, aData ) {
                    authorName = '';
                    documentTitle = '';
                    documentId = '';
                    journalTitle = '';
                    pages = '';
                    portalUrl = '';
                    publicationDateYear = '';
                    publicationType = '';
                    publisher = '';
                    
                    documentId = aData.id;
                    
                    if(aData.authorName) {
                        authorName = aData.authorName;
                    }
                    if(aData.documentTitle) {
                        documentTitle = aData.documentTitle;//'<h4 class="h6"><a href="' + encodeURIComponent(title.replace(/ /g,'-')) + '(' + id + ')">' + title + '</a>';
                    } else {
                        documentTitle = 'untitled';
                    }
                    if(aData.journalTitle) {
                        if(sysLang=='en') {
                            journalTitle = 'In: ' + aData.journalTitle;
                        } else {
                            journalTitle = 'I: ' + aData.journalTitle;
                        }
                    }
                    if(aData.journalTitle && aData.journalNumber) journalTitle += ' ' + aData.journalNumber;                    
                    if(aData.pages) {
                        if(sysLang=='en') {
                            pages = 'p. ' + aData.pages;
                        } else {
                            pages = 's. ' + aData.pages;
                        }
                    }
                    if(aData.portalUrl) {
                        //http://portal.research.lu.se/portal/en/publications/a-general-model-for-jet-fragmentation(84e0113c-9fcb-450e-867e-1eac7f23ff0f).html
                        portalUrl = show + '/' + aData.portalUrl.split('/').pop().split('(').shift();
                    }
                    if(aData.publicationDateYear) {
                        publicationDateYear = aData.publicationDateYear;
                    }
                    if(aData.publicationType) {
                        publicationType = aData.publicationType;
                    }
                    if(aData.publisher) {
                        publisher = aData.publisher;
                    }

                    if(i < 3) {
                        $('#lthSolrLatestPublications').append('<h4 class="h6">' + publicationDateYear + '</h4>');
                        $('#lthSolrLatestPublications').append('<p><a href="' + publicationDetailPage + portalUrl + '">' + documentTitle + '</a><p>');
                    } else {
                        $('#lthSolrAllPublications').append('<p><h4 class="h6">'+publicationType+'</h4>');
                        $('#lthSolrAllPublications').append('<a href="' + publicationDetailPage + portalUrl + '">' + documentTitle + '</a><br/>' + authorName);
                        $('#lthSolrAllPublications').append('<br/>(' + publicationDateYear + ') ' + publisher + '</p>');
                    }

                    i++;
                });
                
                $('.expand-closed').click(function() {
                    $('.expand-content-body, .expand-open').show(200);
                    $(this).toggle();
                });
                $('.expand-open').click(function() {
                    $('.expand-content-body').hide(200);
                    $('.expand-closed').toggle();
                    $(this).toggle();
                });
                //$('#my-accordion').collapse({ parent: true, toggle: true }); 
            } else {
                //$('#lthSolrLatestPublications').parent().parent().parent().hide();
            }
        },
        failure: function(errMsg) {
            console.log(errMsg);
        }
    });
}


function listStatistics()
{
    var program = $('#lth_solr_program').val();
    var round = $('#lth_solr_round').val();
    var syslang = $('html').attr('lang');
    var id,statTermin,statType,statTitle,statCode,statVal1,statVal2,statApplicants,newRow,first;
    var antagna = 0;
    var biExist, biiExist, hpExist, bfExist;

    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: 'listStatistics',
            syslang: syslang,
            dataSettings: {
                pageid: $('body').attr('id'),
                program: program,
                round: round,
                syslang: syslang
            },
            sid: Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            $('#lthsolr_statistics_container').append('<div class="lthPackageLoader"></div>');
        },
        success: function(d) {
            $('.lthPackageLoader').remove();
            
            if(d.data) {
                $.each( d.data, function( key, aData ) {
                    id = '';
                    statApplicants = '';
                    statCode = '';
                    statTermin = '';
                    statTitle = '';
                    statType = '';
                    statVal1 = '';
                    statVal2 = '';
                    
                    antagna=0;
                    if(aData.id) id = aData.id;
                    if(aData.statApplicants) statApplicants = aData.statApplicants;
                    if(aData.statCode) statCode = aData.statCode;
                    if(aData.statTermin) statTermin = aData.statTermin;
                    if(aData.statTitle) statTitle = aData.statTitle;
                    if(aData.statType) statType = aData.statType;
                    if(aData.statVal1) statVal1 = aData.statVal1;
                    if(aData.statVal2) statVal2 = aData.statVal2;
                    if(statVal2) {
                        first = statVal2.shift();
                        statVal2.push(first);
                    }
                    if(program) {
                        
                        newRow = '<div>';
                        newRow += '<p>Antal sökande: ' + statApplicants.split(',')[0];
                        newRow += '<br/>Antal 1a handssökande: ' + statApplicants.split(',')[1];
                        newRow += '</p><table class="table table-sm lth_solr_stat_program_table"><thead class=""><tr></tr></thead><tbody><tr></tr></tbody></table>';
                        newRow += '</div>';
                        $('#lthsolr_statistics_container').append(newRow);
                        
                        var titleObj = { "BI" : "Gymnasiebetyg", "BII" : "Gymnasiebetyg med komplettering", "HP" : "Högskoleprov", "BF" : "Folkhögskola" };
                        
                        for (var i = 0; i < statVal2.length; i++) {
                            var tmpArray = statVal2[i].split(',');
                           
                            /*for (var ii = 0; ii < tmpArray.length; ii++) {
                                if(ii===0) {
                                    newRow += '<br/>' + tmpArray[ii] + ': ';
                                } else {
                                    newRow += tmpArray[ii] + ', ';
                                }
                            }*/

                            if(tmpArray[0] === 'BI' || tmpArray[0] === 'BII' || tmpArray[0] === 'HP' || tmpArray[0] === 'BF') {
                                $('.lth_solr_stat_program_table thead tr').append('<th title="' + titleObj[tmpArray[0]] + '">'+tmpArray[0]+'</th>');
                                $('.lth_solr_stat_program_table tbody tr').append('<td>'+lthSolrRound(tmpArray[1])+'</td>');
                                antagna = antagna + parseInt(tmpArray[2]);
                            }
                            
                        }
                        $('.lth_solr_stat_program_table thead tr').prepend('<th title="Antagna">Ant.</th>');
                        $('.lth_solr_stat_program_table tbody tr').prepend('<td>'+antagna.toString()+'</td>');
                        $('#lthsolr_statistics_container').append('<p><i class="fas fa-info-circle" onclick="lthSolrExplainStat();"></i></p>');
                        $('#lthsolr_statistics_container').append('<p><a href="/utbildning/ansoekan-och-antagning/">Läs mer om antagning</a></p>');
                        /*$('#lthsolr_statistics_container .fa-info-circle').click(function(){
                            alert('BI=Gymnasiebetyg\nBII=Gymnasiebetyg med komplettering\nHP=Högskoleprov\nBF=Folkhögskola');
                        });*/
                    } else {
                        $('#lthsolr_statistics_container tbody').append('<tr></tr>');
                        $('#lthsolr_statistics_container tbody tr:last').append('<td>' + statTitle+'</td>');
                        $('#lthsolr_statistics_container tbody tr:last').append('<td>' + statApplicants.split(',')[0] + '</td>');
                        $('#lthsolr_statistics_container tbody tr:last').append('<td>' + statApplicants.split(',')[1] + '</td>');

                        var ii = 0;
                        biExist='-';
                        biiExist='-';
                        hpExist='-';
                        bfExist='-';
                        for (var i = 0; i < statVal2.length; i++) {
                            var tmpArray = statVal2[i].split(',');
                            /*for (var ii = 0; ii < tmpArray.length; ii++) {
                                if(ii!==0) {
                                    newRow += '<td>' + tmpArray[ii] + '</td>';
                                }
                            }*/
                            if(tmpArray[0] === 'BI') biExist=tmpArray[1];
                            if(tmpArray[0] === 'BII') biiExist=tmpArray[1];
                            if(tmpArray[0] === 'HP') hpExist=tmpArray[1];
                            if(tmpArray[0] === 'BF') bfExist=tmpArray[1];
                            antagna = antagna + parseInt(tmpArray[2]);
                            ii++;
                        }
                        $('#lthsolr_statistics_container tbody tr:last').append('<td>'+biExist+'</td>');
                        $('#lthsolr_statistics_container tbody tr:last').append('<td>'+biiExist+'</td>');
                        $('#lthsolr_statistics_container tbody tr:last').append('<td>'+hpExist+'</td>');
                        $('#lthsolr_statistics_container tbody tr:last').append('<td>'+bfExist+'</td>');
                        /*var rest = 4 - ii;
                        
                        for (var ii = 0; ii < rest; ii++) {
                            $('#lthsolr_statistics_container tbody tr:last').append('<td>'+ii+'</td>');
                        }*/
                        /*{
                            $('#lthsolr_statistics_container tbody tr:last').append('<td></td>');
                        }*/
                        //console.log(rest+';'+ii);
                        $('#lthsolr_statistics_container tbody tr:last td:eq(2)').after('<td>'+antagna+'</td>');
                        //newRow += '</tr>';
                        //$('#lthsolr_statistics_container tbody').append(newRow);
                    }
                });
            }
        }
    });
}


function lthSolrExplainStat()
{
    alert('BI=Gymnasiebetyg\nBII=Gymnasiebetyg med komplettering\nHP=Högskoleprov\nBF=Folkhögskola\n*=Samtliga behöriga sökanden antogs');
}


function lthSolrRound(x)
{
    if(parseFloat(x)) {
        //input = Math.round(input * 100) / 100;
        x = Number.parseFloat(x).toFixed(2);
    }
    return x;
}


function listJobs()
{
    var syslang = $('html').attr('lang');
    var endDate,id,jobType,link,refNr,jobTitle;

    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: 'listJobs',
            syslang: syslang,
            dataSettings: {
                syslang: syslang,
                pageid: $('body').attr('id')
            },
            sid: Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            $('#lthsolr_job_container > .col').append('<div class="lthPackageLoader"></div>');
        },
        success: function(d) {
            $('.lthPackageLoader').remove();
            
            if(d.data) {
                $.each( d.data, function( key, aData ) {
                    endDate = '';
                    id = '';
                    jobTitle = '';
                    link = '';
                    refNr = '';

                    if(aData.endDate) endDate = aData.endDate.substr(0,10);
                    if(aData.id) id = aData.id;
                    if(aData.jobTitle) jobTitle = aData.jobTitle;
                    if(aData.jobType) jobType = aData.jobType;
                    if(aData.refNr) refNr = aData.refNr;
                    
                    if(syslang==='sv') {
                        link = 'visa/'+encodeURI(jobTitle+'('+refNr.replace('/','-')+')');
                    } else {
                        link = 'show/'+encodeURI(jobTitle+'('+refNr.replace('/','-')+')');
                    }
                    
                    var template = $('#solrJobTemplate').html();
                    template = template.replace('###endDate###', endDate);
                    template = template.replace('###type###', jobType[0]);
                    template = template.replace('###category###', jobType[1]);
                    template = template.replace('###link###', link);
                    template = template.replace(/###jobTitle###/g, jobTitle);
                    $('#lthsolr_job_container > .col').append(template);
                });
            }
        }
    });
}


function showJob()
{
    var syslang = $('html').attr('lang');
    var scope = $('#lth_solr_scope').val();
    var abstract,endDate,id,jobTitle,jobType,link,refNr,jobTitle,loginAndApplyURI;
    var jobAnstForm,jobTilltrade,jobLoneform,jobAntal,jobSysselsattningsgrad,jobOrt,jobLan,jobLand,jobReferensnummer,jobKontakt;
    var jobPublicerat,jobSistaAnsokningsdag,jobPositionContact,jobUnionRepresentative;

    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: 'showJob',
            syslang: syslang,
            dataSettings: {
                syslang: syslang,
                scope: scope,
                pageid: $('body').attr('id')
            },
            sid: Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            
        },
        success: function(d) {
            if(d.data.abstract) {
                //$('main > div > div:eq(0)').empty();
                abstract = '';
                jobTitle = '';
                loginAndApplyURI = '';
                if(d.data.abstract) abstract = d.data.abstract;
                if(d.data.endDate) jobSistaAnsokningsdag = d.data.endDate;
                if(d.data.jobTitle) jobTitle = d.data.jobTitle;
                if(d.data.jobType) {
                    jobAnstForm = d.data.jobType[0];
                    jobTilltrade = d.data.jobType[2];
                    jobLoneform = d.data.jobType[3];
                    jobAntal = d.data.jobType[4];
                    jobSysselsattningsgrad = d.data.jobType[5];
                    jobOrt = d.data.jobType[6];
                    jobLan = d.data.jobType[7];
                    jobLand = d.data.jobType[8];
                }
                if(d.data.jobPositionContact) jobPositionContact = d.data.jobPositionContact;
                if(d.data.jobUnionRepresentative) jobUnionRepresentative = d.data.jobUnionRepresentative;
                if(d.data.endDate) jobSistaAnsokningsdag = d.data.endDate.substr(0,10);
                if(d.data.loginAndApplyURI) loginAndApplyURI = decodeURIComponent(d.data.loginAndApplyURI);
                if(d.data.published) jobPublicerat = d.data.published.substr(0,10);
                if(d.data.refNr) jobReferensnummer = d.data.refNr;
                
                //console.log(d.data.abstract);
                $('.article h1').text(jobTitle).attr('style', 'margin-bottom:18px !important;max-width:650px;');
                $('.lthsolr_job_apply_button').wrap('<a href="'+loginAndApplyURI+'"></a>').text(lth_solr_messages.applyButtonText).show();
                if(syslang==='sv') {
                    $('.breadcrumb li:last').removeClass('active').wrapInner('<a href="/'+lth_solr_messages.job+'/"></a>');
                } else {
                    $('.breadcrumb li:last').removeClass('active').wrapInner('<a href="/english/'+lth_solr_messages.job+'/"></a>');
                }
                $('.breadcrumb').append('<li class="breadcrumb-item active">'+jobTitle+'</li>');
                                
                $('#lthsolr_job_container > .col').prepend(abstract);
                if(jobAnstForm) $('#lthsolr_job_container > .col > table > tbody').append('<tr><th scope="row" class="xx">'+lth_solr_messages.jobAnstForm+'</th><td>'+jobAnstForm+'</td></tr>');
                if(jobTilltrade) $('#lthsolr_job_container > .col > table > tbody').append('<tr><th scope="row" class="xx">'+lth_solr_messages.jobTilltrade+'</th><td>'+jobTilltrade+'</td></tr>');
                if(jobLoneform) $('#lthsolr_job_container > .col > table > tbody').append('<tr><th scope="row" class="xx">'+lth_solr_messages.jobLoneform+'</th><td>'+jobLoneform+'</td></tr>');
                if(jobAntal) $('#lthsolr_job_container > .col > table > tbody').append('<tr><th scope="row" class="xx">'+lth_solr_messages.jobAntal+'</th><td>'+jobAntal+'</td></tr>');
                if(jobSysselsattningsgrad) $('#lthsolr_job_container > .col > table > tbody').append('<tr><th scope="row" class="xx">'+lth_solr_messages.jobSysselsattningsgrad+'</th><td>'+jobSysselsattningsgrad+'</td></tr>');
                if(jobOrt) $('#lthsolr_job_container > .col > table > tbody').append('<tr><th scope="row" class="xx">'+lth_solr_messages.jobOrt+'</th><td>'+jobOrt+'</td></tr>');
                if(jobLan) $('#lthsolr_job_container > .col > table > tbody').append('<tr><th scope="row" class="xx">'+lth_solr_messages.jobLan+'</th><td>'+jobLan+'</td></tr>');
                if(jobLand) $('#lthsolr_job_container > .col > table > tbody').append('<tr><th scope="row" class="xx">'+lth_solr_messages.jobLand+'</th><td>'+jobLand+'</td></tr>');
                if(jobReferensnummer) $('#lthsolr_job_container > .col > table > tbody').append('<tr><th scope="row" class="xx">'+lth_solr_messages.jobReferensnummer+'</th><td>'+jobReferensnummer+'</td></tr>');
                if(jobPositionContact) {
                    for (var pc = 0; pc < jobPositionContact.length; pc++) {
                        if(pc===0) {
                            $('#lthsolr_job_container > .col > table > tbody').append('<tr><th scope="row" class="xx">'+lth_solr_messages.jobKontakt+'</th><td>'+jobPositionContact[pc]+'</td></tr>');
                        } else {
                            $('#lthsolr_job_container > .col > table > tbody').append('<tr><td></td><td>'+jobPositionContact[pc]+'</td></tr>');
                        }
                    };
                }
                if(jobUnionRepresentative) {
                    for (var ur = 0; ur < jobUnionRepresentative.length; ur++) {
                        if(ur===0) {
                            $('#lthsolr_job_container > .col > table > tbody').append('<tr><th scope="row" class="xx">'+lth_solr_messages.jobFack+'</th><td>'+jobUnionRepresentative[ur]+'</td></tr>');
                        } else {
                            $('#lthsolr_job_container > .col > table > tbody').append('<tr><td></td><td>'+jobUnionRepresentative[ur]+'</td></tr>');
                        }
                    };
                }
                if(jobPublicerat) $('#lthsolr_job_container > .col > table > tbody').append('<tr><th scope="row" class="xx">'+lth_solr_messages.jobPublicerat+'</th><td>'+jobPublicerat+'</td></tr>');
                if(jobSistaAnsokningsdag) $('#lthsolr_job_container > .col > table > tbody').append('<tr><th scope="row" class="xx">'+lth_solr_messages.jobSistaAnsokningsdag+'</th><td>'+jobSistaAnsokningsdag+'</td></tr>');
            }
        }
    });
}


function listCompare(action)
{
    var syslang = $('html').attr('lang');
    var roundId = $('#lth_solr_round').val();
    var scope = $('#lth_solr_scope').val();
    var courseCode,courseTitle,courseType,courseYear,credit,
        homepage,id,nextId,optional,prevId,programCode,programDirection,
        programDirectionGeneral,programTitle,ratingScale;
    var i=0, ii=0;

    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: action,
            dataSettings: {
                syslang: syslang,
                roundId: roundId,
                scope: scope,
                pageid: $('body').attr('id')
            },
            sid: Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            $('#lthsolr_compare_container').prepend('<img class="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            $('.lthsolr_loader').remove();
            if(d.data) {
                $.each( d.data, function( programKey, programData ) {
                    if(Object.keys(d.data).length > 1) {
                        $('#lthsolr_compare_container').append('<div id="'+programKey+'" class="colcard col-sm">'+programKey+'</div>');
                    } else {
                        $('#lthsolr_compare_container').append('<div class="col"><table class="table table striped" id="'+programKey+'"><thead class="thead-dark"><tr></tr></thead><tbody><tr></tr></tbody></table></div>');
                    }
                    $.each( programData, function (arskursKey, arskursData) {
                        i=0;
                        if(arskursData) {
                            if(Object.keys(d.data).length > 1) {
                                $("[id='"+programKey+"']").append('<div id="'+programKey+arskursKey+'" class="card-header">Årskurs '+arskursKey+'</div>');
                            } else {
                                $("[id='"+programKey+"'] > thead > tr").append('<th>Årskurs '+arskursKey+'</th>');
                                $("[id='"+programKey+"'] > tbody > tr").append('<td><div id="'+programKey+arskursKey+'"></div></td>');
                            }
                        }                           
                        $.each( arskursData, function (kurstypKey, kurstypData) {
                            kurstypKey = kurstypKey.substr(1,kurstypKey.length);
                            if(kurstypKey.substr(0,14) === 'Specialisering' || kurstypKey.substr(0,14) === 'Examensarbeten') {
                                if(i===0 && kurstypKey.substr(0,14) === 'Specialisering') {
                                    $("[id='"+programKey+arskursKey+"']").append('<div><b>Specialiseringar</b></div>');
                                }
                                if(kurstypKey.substr(0,14) === 'Examensarbeten' && ii === 0) {
                                    $("[id='"+programKey+arskursKey+"']").append('<div><b>Examensarbeten</b></div>');
                                    ii++;
                                }
                                $("[id='"+programKey+arskursKey+"']").append('<div class="accordion" id="accordion'+i+'"><div class="card">'+
                                '<div class="card-header" id="heading'+i.toString()+'"><h5><button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse'+i.toString()+
                                '" aria-expanded="true" aria-controls="collapse'+i.toString()+'">'+
                                    '<div class="float-right"><span class="collapse-hide"><i class="fal fa-chevron-up"></i></span><span class="collapse-show">'+
                                    '<i class="fal fa-chevron-down"></i></span></div>'+
                                    titleCase(kurstypKey.replace('Specialisering - ','')) +
                                  '</button></h5></div><div id="collapse'+i.toString()+'" class="collapse" aria-labelledby="heading'+i.toString()+'" data-parent="#accordion'+i.toString()+'"><div class="card-body">'+
                                    '<ul id="'+programKey+arskursKey+kurstypKey+'" class="list-group list-group-flush"></ul></div></div></div></div>');
                                i++;
                            } else {
                                $("[id='"+programKey+arskursKey+"']").append('<div><b>' + kurstypKey + '</b></div><ul id="'+programKey+arskursKey+kurstypKey+'" class="list-group list-group-flush"></ul>');
                            }
                            $.each( kurstypData, function (kursKey, kursData) {
                                /*if(optionalData) {
                                    if(arskursKey < 4) {
                                        $("[id='"+programKey+arskursKey+"']").append('<div><b>' + titleCase(optionalKey.replace('_','-').replace('valfri','Valfria kurser').replace('obligatorisk','Obligatoriska kurser')) + '</b></div><ul id="'+programKey+arskursKey+inriktningKey+optionalKey+'" class="list-group list-group-flush"></ul>');
                                    } else {
                                        $("[id='"+programKey+arskursKey+"']").append('<div class="accordion" id="accordion'+i+'"><div class="card">'+
                                        '<div class="card-header" id="heading'+i.toString()+'"><h5><button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse'+i.toString()+
                                        '" aria-expanded="true" aria-controls="collapse'+i.toString()+'">'+
                                            '<div class="float-right"><span class="collapse-hide"><i class="fal fa-chevron-up"></i></span><span class="collapse-show">'+
                                            '<i class="fal fa-chevron-down"></i></span></div>'+
                                            inriktningKey.replace('Allmän inriktning','Valfria kurser') +
                                          '</button></h5></div><div id="collapse'+i.toString()+'" class="collapse" aria-labelledby="heading'+i.toString()+'" data-parent="#accordion'+i.toString()+'"><div class="card-body">'+
                                            '<ul id="'+programKey+arskursKey+inriktningKey+optionalKey+'" class="list-group list-group-flush"></ul></div></div></div></div>');
                                        i++;
                                    }
                                }
                                $.each( optionalData, function (courseKey, kursData) {*/
                                    courseCode = '';
                                    courseTitle = '';
                                    courseType = '';
                                    courseYear = '';
                                    credit = '';
                                    homepage = '';
                                    id = '';
                                    nextId = '';
                                    optional = '';
                                    prevId = '';
                                    programCode = '';
                                    programDirection = '';
                                    programDirectionGeneral = '';
                                    programTitle = '';
                                    ratingScale = '';

                                    if(kursData.courseCode) courseCode = kursData.courseCode.toUpperCase();
                                    if(kursData.courseTitle) courseTitle = kursData.courseTitle;
                                    if(kursData.courseType) courseType = kursData.courseType;
                                    if(kursData.courseYear) courseYear = kursData.courseYear;
                                    if(kursData.credit) credit = kursData.credit;
                                    if(kursData.id) id = kursData.id;
                                    if(kursData.nextId) nextId = kursData.nextId;
                                    if(kursData.optional) optional = kursData.optional;
                                    if(kursData.prevId) prevId = kursData.prevId;
                                    if(kursData.programDirection) programDirection = kursData.programDirection;
                                    if(kursData.programDirectionGeneral) programDirectionGeneral = kursData.programDirectionGeneral;
                                    if(kursData.ratingScale) ratingScale = kursData.ratingScale;

                                    /*if(courseType === 'EXAMENSARBETE') {
                                        $("[id='"+programKey+arskursKey+kurstypKey+"']").parent().parent().prev().find('h5 button').text('Examensarbete');
                                    }*/
                                    
                                    $("[id='"+programKey+arskursKey+kurstypKey+"']").append('<li id="'+id+'" data-prevId="'+prevId+'" data-nextId="'+nextId+'" class="list-group-item">'+courseCode+', '+courseTitle+', '+credit.replace('.0','')+'hp</li>');
                                    $("[id='"+id+"']").click(function(){
                                        $('#compareModal').modal('toggle');
                                        showCompare(this.id);
                                    });
                                //});
                            });
                        });
                    }); 
                });
            }
        }
    });
}


function showCompare(kursId)
{
    var syslang = $('html').attr('lang');
    var abstract,courseCode,courseTitle,courseYear,credit,homepage,id,optional,programCode,programDirection,programTitle,ratingScale;

    /*if($(scope).next().attr('id')) {
        console.log('Next: ' + $(scope).next().attr('id'));
    } else if($(scope).parent().next().next().attr('id')) {
        console.log('Next: ' + $(scope).parent().next().next('').find('li').attr('id'));
    } else if($(scope).parent().parent().parent().next().find('li').attr('id')) {
        console.log('Next: ' + $(scope).parent().parent().parent().next().find('li').attr('id'));
    } else if($(scope).parent().parent().parent().parent().parent().next().next().find('li').attr('id')) {
        console.log('Next: ' + $(scope).parent().parent().parent().parent().parent().next().next().find('li').attr('id'));
    } else if($(scope).parent().parent().parent().parent().parent().parent().parent().next().find('li').attr('id')) {
        console.log('Next: ' + $(scope).parent().parent().parent().parent().parent().parent().parent().next().find('li').attr('id'));
    }*/
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: 'showCompare',
            dataSettings: {
                syslang: syslang,
                scope: kursId,
                pageid: $('body').attr('id')
            },
            sid: Math.random(),
        },
        dataType: 'json',
        error : function(jq, st, err) {
            alert(st + " : " + err);
        },
        beforeSend: function () {
            //$('#lthsolr_compare_container div').prepend('<img class="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            //$('.lthsolr_loader').remove();
            if(d.data) {
                $.each( d.data, function( key, aData ) {
                    abstract = '';
                    courseCode = '';
                    courseTitle = '';
                    courseYear = '';
                    credit = '';
                    homepage = '';
                    
                    optional = '';
                    programCode = '';
                    programDirection = '';
                    programTitle = '';
                    ratingScale = '';
                    if(aData.abstract) abstract = aData.abstract;
                    if(aData.courseCode) courseCode = aData.courseCode.toUpperCase();
                    if(aData.courseYear) courseYear = aData.courseYear;
                    if(aData.courseTitle) courseTitle = aData.courseTitle;
                    if(aData.credit) credit = aData.credit;
                    if(aData.programTitle) programTitle = aData.programTitle;
                    
                    $('#compareModal .modal-title').html(courseTitle);
                    $('#compareModal .modal-title').attr('title',courseTitle);
                    $('#compareModal .modal-title').append('<br/><span style="font-size:14px;font-weight:bold;">' +  courseCode + ', ' + credit + 'hp, ' + programTitle + ' årskurs ' + courseYear + '</span>');
                    $('#compareModal .modal-body').html(abstract);

                    if($('#'+kursId).attr('data-prevId').length>0) {
                        $(".lth_solr_prev_course").off().removeClass('disabled').click(function(){
                            showCompare($('#'+kursId).attr('data-prevId'));
                        });
                    } else {
                        $(".lth_solr_prev_course").addClass('disabled');
                    }
                    if($('#'+kursId).attr('data-nextId').length>0) {
                        $(".lth_solr_next_course").off().removeClass('disabled').click(function(){
                            showCompare($('#'+kursId).attr('data-nextId'));
                        });
                    } else {
                        $(".lth_solr_next_course").addClass('disabled');
                    }
                });
            }
        }
    });
}


function mobileCheck()
{
    var check = false;
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
        check=true;
    }
    //testmode start
    //check=true;
    //testmode end
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
    
    var publicationCategories = $('#lth_solr_publicationCategories').val();
    
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
            publicationCategories: publicationCategories,
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
                    $('#exportModal .modal-body').css("float","left").after('<div style="float:left;padding:25px;0px;0px;25px;"><ul id="exportParts"></ul></div>');
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
    if(!facet) facet = '';
    var inputFacet = facet;
    var exportArray = ["firstName","lastName","title","phone","email","organisationName","homepage","roomNumber","mobile"];
    var limitToStandardCategories = $('#lth_solr_limitToStandardCategories').val();
    var showPictures = $('#lth_solr_showPictures').val();
    var heritage = $('#lth_solr_heritage').val();
    var thisGroupOnly = $('#lth_solr_thisGroupOnly').val();
    var primaryRoleOnly = $('#lth_solr_primaryRoleOnly').val();

    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: 'listStaff',
            tableStart: tableStart,
            tableLength: tableLength,
            pid: $('#pid').val(),
            pageid: $('body').attr('id').replace('p',''),
            scope: scope,
            syslang: syslang,
            query: query,
            categories: $('#lth_solr_categories').val(),
            thisGroupOnly: thisGroupOnly,
            primaryRoleOnly: primaryRoleOnly,
            custom_categories: $('#lth_solr_custom_categories').val(),
            introThisPage: $('#introThisPage').val(),
            //addPeople : $('#addPeople').val(),
            facet: facet,
            limitToStandardCategories: limitToStandardCategories,
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
            
            $('#lth_solr_facet_container ul li').remove();
            
            $('#lth_solr_export').show(500);
        },
        success: function(d) {
            var staffDetailPage = 'visa';
            if(syslang=='en') {
                staffDetailPage = 'show';
            }
                
            if(d.data) {
                var i = 0;
                var ii = 0;
                var lastHeight = '';
                var thisHeight = '';
                var lastId = '';
                var maxClass = '';
                var more = '';
                var count = '';
                var facet = '';
                var facetHeader = '';
                var content = '';
                var more = '<p class="maxlist-more"></p>';
                
                if(d.facet) {
                    
                    //if($('.item-list').length == 0 || 1+1===2) {
                        if(mobileCheck()) {
                            if($('#lth_solr_facet_container').length == 0) {
                                $('#lthsolr_staff_container').append('<div id="lth_solr_facet_container">'+
                                    '<div style="margin-top:15px;" class="input-group">'+
                                        '<span class="input-group-addon" id="basic-addon1"><i class="fa fa-search fa-sm slsGray20"></i></span>'+
                                        //'<i class="fa fa-search fa-lg slsGray50"></i>' +
                                        '<input type="text" style="font-size:12px;" class="form-control" id="lthsolr_staff_filter" placeholder="" value="" />' +
                                        '</div>'+
                                        '<ul style=""><li><i class="fa fa-angle-right fa-sm slsGray20"></i><a href="javascript:" onclick="$(\'.maxlist-hidden\').toggle(500);">'+lth_solr_messages.moreFilteringOptions+'</a></li></ul></div>');
                                $('#lthsolr_staff_filter').keyup(function() {
                                    var noQuery;
                                    if($(this).val().trim() === '') {
                                        noQuery = true;
                                    } else {
                                        noQuery = false;
                                    }
                                    listStaff(0, getFacets(), $(this).val().trim(), noQuery, '');
                                });
                            }
                        } else {
                            if($('#lth_solr_facet_container').length == 0) {
                                $('#content_navigation').append('<div id="lth_solr_facet_container">'+
                                        '<b>' + lth_solr_messages.filterSearchResult+'</b>'+
                                        '<div style="margin-top:15px;" class="input-group">'+
                                        '<span class="input-group-addon" id="basic-addon1"><i class="fa fa-search fa-sm slsGray20"></i></span>'+
                                        //'<i class="fa fa-search fa-lg slsGray50"></i>' +
                                        '<input type="text" style="font-size:12px;" class="form-control" id="lthsolr_staff_filter" placeholder="" value="" />'+
                                        '</div>'+
                                        '<ul style="border-top:1px #dedede solid;margin-top:15px;padding-top:7px;max-width:210px;"></ul></div>');
                                $('#lthsolr_staff_filter').keyup(function() {
                                    var noQuery;
                                    if($(this).val().trim() === '') {
                                        noQuery = true;
                                    } else {
                                        noQuery = false;
                                    }
                                    listStaff(0, getFacets(), $(this).val().trim(), noQuery, '');
                                });
                            }
                        }
                        $.each( d.facet, function( key, value ) {
                            $.each( value, function( key1, value1 ) {
                                /*if(i > 4) {
                                    maxClass = ' class="maxlist-hidden"';
                                    more = '<p class="maxlist-more"><i class="fa fa-chevron-right"></i><a href="javascript:">' + lth_solr_messages.more + '</a></p>';
                                }*/
                                if(mobileCheck()) maxClass = ' class="maxlist-hidden"';
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
                                    //content += '<li style="width:100%;">';
                                    content += '<li' + maxClass + '><label>';
                                    content += facet.capitalize().replace(/_/g, ' ') + '&nbsp;[' + count + '] ';
                                    content += '<input type="checkbox" class="lth_solr_facet" name="lth_solr_facet" value="' + key + '###' + facet + '"' + facetCheck + '>';
                                    content += '</label></li>';
                                }
                                i++;
                            });

                            $('#lth_solr_facet_container ul').append('<li' + maxClass + ' style="width:100%;"><b>'+facetHeader+'</b></li>' + content + '' + more);
                            /*$('.lthsolr_facet_close').click(function() {
                                $('#lth_solr_facet_container').toggle(500);
                                //$("#lthsolr_staff_container").toggleClass('expand', 500);
                            });*/
                            i=0;
                            maxClass='';
                            more='';
                            content = '';
                        });
                        createFacetClick('listStaff');
                    //}
                }
            
                $.each( d.data, function( key, aData ) {
                    var template = $('#solrStaffTemplate').html();


                    var guid = '';
                    var image = '';
                    var uuid = '';
                    var phone = '', homepage = '', organisationName = '', primaryVroleOu = '', primaryVroleTitle = '', primaryVroleOrgid = '',primaryVrolePhone = '';
                    if(aData.guid) guid = aData.guid[0];
                   
                    if(aData.uuid) uuid = aData.uuid;
                    if(!uuid && guid) {
                        uuid = guid;
                    }
                    
                    template = template.replace('###id###', uuid);

                    var displayName = aData.firstName + ' ' + aData.lastName;
                    
                    var homepage = window.location.href + staffDetailPage + '/' + displayName.replace(' ','-') + '('+uuid+')';
                    if(aData.homepage) {
                        homepage = aData.homepage;
                    }

                    if(aData.email) template = template.replace(/###email###/g, aData.email[0]);

                    var affiliation='';
                    
                    heritage = decodeURIComponent(heritage);

                    for(i=0; i<aData.organisationId.length; i++) {
                        if((heritage.indexOf(aData.organisationId[i]) > 0 && thisGroupOnly==0) || (scope.indexOf(aData.organisationId[i]) > 0 && thisGroupOnly==1)) {
                            if(affiliation) affiliation += '<br />';
                            if(aData.title) {
                                if(aData.title[i]) {
                                    affiliation += titleCase(aData.title[i]);
                                }
                            }
                            if(aData.organisationName) {
                                if(aData.organisationName[i]) {
                                    organisationName = aData.organisationName[i];
                                }
                            }
                            if(aData.roomNumber) {
                                if(aData.roomNumber[i]) {
                                    organisationName += ' (' + lth_solr_messages.room + ' ' + aData.roomNumber[i] + ')';
                                }
                            }
                            if(aData.phone) {
                                    if(aData.phone[i] && aData.phone[i] != 'NULL') {
                                        phone = addBreak(aData.phone[i]);
                                        if(phone) phone = phone.replace('+4646222', '+46 46 222 ').replace(/(.{2}$)/, ' $1');
                                    }                               
                            }
                            
                            if(aData.mobile) {
                                if(aData.mobile[i] && aData.mobile[i] !== 'NULL') {
                                    phone += addBreak('+46 ' + aData.mobile[i].replace(/ /g, '').replace('+46','').replace(/(\d{2})(\d{3})(\d{2})(\d{2})/, "$1 $2 $3 $4"));
                                }
                            }
                            
                            if(aData.organisationLeaveOfAbsence) {
                                if(aData.organisationLeaveOfAbsence[i]==='1') {
                                    displayName = displayName + '(' + lth_solr_messages.organisationLeaveOfAbsence + ')';
                                }
                            }
                        }
                    }

                    template = template.replace(/###displayName###/g, '<a href="'+homepage+'">' + displayName + '</a>');

                    if(primaryRoleOnly==1 && aData.primaryVroleTitle) affiliation = titleCase(aData.primaryVroleTitle);

                    if(primaryRoleOnly==1 && aData.primaryVroleOu) {
                        affiliation += addComma(aData.primaryVroleOu);
                    } else if(organisationName) {
                        affiliation += addComma(organisationName);
                    }
                    
                    if(primaryRoleOnly==1 && aData.primaryVrolePhone) {
                        affiliation += aData.primaryVrolePhone;
                    } else if(phone) {
                        affiliation += phone;
                    }
                    template = template.replace('###affiliation###', affiliation);

                    if(aData.homepage) {
                        homepage = '<a data-homepage="' + aData.homepage + '" href="' + aData.homepage + '"><img class="lthsolr_home" src="/typo3conf/ext/lth_solr/res/home.png" /></a>';
                    } else {
                        homepage = '';
                    }
                    template = template.replace('###homepage###', homepage);

                    if(showPictures==='yes') {
                        image = '<img src="/typo3conf/ext/lth_solr/res/noimage.gif" />';
                        if(aData.image) {
                            image = '<img id="'+ii+'" src="' + aData.image + '" />';
                        }
                    }
                    template = template.replace('###image###', image);
                    
                    template = template.replace('###lth_solr_intro###', aData.intro.replace('\n','<br />'));

                    /*
                    template = template.replace('###roomNumber###', roomNumber);*/
                    $('#lthsolr_staff_container').append(template);
                    //console.log(lastHeight+';'+lastId);

                    if(!mobileCheck()) {
                        if(isEven(ii)) {
                            thisHeight = $('#'+uuid).height();
                            lastHeight = thisHeight;
                            lastId = uuid;
                        } else {
                            if($('#'+uuid).height() > lastHeight) {
                                $('#'+lastId).height($('#'+uuid).height());
                            } else {
                                $('#'+uuid).height(lastHeight);
                            }
                        }
                    }
                    ii++;
                });
                $('.lthsolr_loader').remove();
                
                $('#lthsolr_staff_header').html('<span style="">1-' + maxLength(parseInt(tableStart),parseInt(tableLength),parseInt(d.numFound)) + ' ' + lth_solr_messages.of + ' ' + d.numFound + '</span>');
                if($('#lth_solr_lu').val() === "yes" && $('.fa-download').length < 1) {
                    if(mobileCheck()) {
                            /*$('#lthsolr_publications_container').append('<div><button style="height:40px;" class="btn btn-default btn-lg btn-block">' + 
                                    lth_solr_messages.export + 
                                    '<i style="cursor:pointer;" class="fa fa-download fa-lg slsGray50"></i>' +
                                    '</button></div>');
                            */
                    } else {
                        $('#lth_solr_facet_container').append('<div id="lth_solr_export"><div style="margin-top:15px;border-top:1px #dedede solid;padding-top:7px;">'+
                            '<b>' + lth_solr_messages.export + '</b></div><i style="cursor:pointer;" class="fa fa-download fa-lg slsGray50"></i></div>');
                    }
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
                        onclick="listStaff(' + (parseInt(tableStart) + parseInt(tableLength)) + ',getFacets(),$(\'#lthsolr_staff_filter\').val(),\'\',\'more\');">' + 
                            lth_solr_messages.show_more + ' ' + lth_solr_messages.people + 
                            ' <span class="glyphicon glyphicon-chevron-down"></span></button>';
                    /*if(d.numFound < 300) {
                        tempMore += ' | <a href="javascript:" onclick="$(\'#lth_solr_no_items\').val(' + d.numFound + '); listStaff(' + (parseInt(tableStart) + parseInt(tableLength)) + ',\'\',\'\',\'\',\'more\');">' + lth_solr_messages.show_all + ' ' + d.numFound + '</a>';
                    }*/
                    tempMore += '</div>';
                    $('#lthsolr_staff_container').append(tempMore);
                }
                
                if(!mobileCheck()) {
                    //$('#lthsolr_staff_container').parent().height($('#lthsolr_staff_container').height());
                    //$('#lth_solr_facet_container').height($('#lthsolr_staff_container').height());
                    /*$('#lthsolr_staff_container, #lth_solr_facet_container').css('float','left');
                    $('#lthsolr_staff_container').css('width','500px');
                    $('#lth_solr_facet_container').css('width','200px');*/
                }
            } else {
                $('#lth_solr_export').hide(500);
            }
            
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


function searchLong(term, startPeople, startPages, startCourses, more, webSearchScope)
{
    if(term.replace('"','').length < 2) return false;
    var syslang = $('html').attr('lang');
    var tableLength = 6;
    var webSearchScope = $('#webSearchScope').val();
    var linkStaffDetailPage = $('#linkStaffDetailPage').val();
    var template;
    var lastHeight = '';
    var thisHeight = '';
    var lastId = '';
    
    $('.content_navigation').hide();
    if($('#content_navigation').length==0) $('#content').prepend('<nav id="content_navigation" class="grid-8 alpha hide-xs"></nav>');
    //$('#text_wrapper').removeClass('grid-23').removeClass('omega').addClass('grid-31');

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
            if(mobileCheck()) {
                if($('#lth_solr_facet_container').length == 0) {
                    $('#lthsolr_search_container').prepend('<div style="margin-bottom:15px;" id="lth_solr_facet_container">'+
                        '<div style="margin-top:15px;" class="input-group">'+
                            '<span class="input-group-addon" id="basic-addon1"><i class="fa fa-search fa-sm slsGray20"></i></span>'+
                            //'<i class="fa fa-search fa-lg slsGray50"></i>' +
                            '<input type="text" style="font-size:12px;" class="form-control" id="searchSiteMain" placeholder="" value="" />' +
                            '</div>'+
                            '</div>');
                    $('#searchSiteMain').keyup(function() {
                        searchLong($(this).val(), 0, 0, 0, false);
                    });
                }
            } else {
                if($('#lth_solr_facet_container').length == 0) {
                    $('#content_navigation').append('<div id="lth_solr_facet_container">'+
                            '<b>' + lth_solr_messages.filterSearchResult+'</b>'+
                            '<div style="margin-top:15px;" class="input-group">'+
                            '<span class="input-group-addon" id="basic-addon1"><i class="fa fa-search fa-sm slsGray20"></i></span>'+
                            //'<i class="fa fa-search fa-lg slsGray50"></i>' +
                            '<input type="text" style="font-size:12px;" class="form-control" id="searchSiteMain" placeholder="" value="" />'+
                            '</div>'+
                            '<ul style="border-top:1px #dedede solid;margin-top:15px;padding-top:7px;max-width:210px;"></ul></div>');
                    $('#searchSiteMain').keyup(function() {
                        searchLong($(this).val(), 0, 0, 0, false);
                    });
                }
            }
            
            if(startPeople == 0 && more == 0) {
                $('#lthsolr_staff_container').parent().show();
                $('#lthsolr_staff_container').html('').append('<div class="loader"></div>');
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
           
            /*if(startCourses == 0 && more == 0) {
                $('#lthsolr_courses_container').parent().show();
                $('#lthsolr_courses_container tbody').html('').append('<tr><td class="loader"></td></tr>');
            }*/
            
            //$('.lthsolr_more').replaceWith('<img class="lthsolr_loader" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            var i = 0;
            var maxClass = '';
            var moreContent = '';
            var count = '';
            var facet = '';
            var content = '';
            var id, title, teaser, url, link, label, label_sv, label_en;

            //STAFF**************************************************************************************
            var tableCounter = 3;
            var indexCounter = 1+startPeople;
            var ii=0;
            
            $('#lthsolr_staff_container .loader').remove();
            var staffDetailPage = 'visa/';
            if(syslang=='en') {
                staffDetailPage = 'show/';
            }
            if(d.peopleData.length > 0) {
                //$('.lth_solr_res').append('<li>People</li>');
                $.each(d.peopleData, function( key, aData ) {
                    var affiliation='';
                    var guid = '';
                    if(aData.guid) guid = aData.guid[0];
                    var uuid = '';
                    if(aData.uuid) uuid = aData.uuid;
                    if(!uuid && guid) {
                        uuid = guid;
                    }
                    var displayName = aData.firstName + ' ' + aData.lastName;
                    var title = aData.title[0];
                    template = $('#solrStaffTemplate').html();
                    
                    template = template.replace('###id###', uuid);
                    
                    var image = '', homepage='';
                    if(aData.imageId != 0 && aData.imageId != null) {
                       image = aData.imageId;
                    } else if(aData.lucrisPhoto) {
                       image = aData.lucrisPhoto;
                    }

                    image = '<img src="/typo3conf/ext/lth_solr/res/noimage.gif" />';
                    if(aData.image) {
                        image = '<img id="'+ii+'" src="' + aData.image + '" />';
                    }
                    template = template.replace('###image###', image);
                    
                    if(aData.homepage) {
                        homepage = '<a data-homepage="' + aData.homepage + '" href="' + aData.homepage + '"><img class="lthsolr_home" src="/typo3conf/ext/lth_solr/res/home.png" /></a>';
                    }
                    template = template.replace('###homepage###', homepage);

                    if(linkStaffDetailPage==="yes") {
                        template = template.replace(/###displayName###/g, '<a href="'+location.protocol + '//' + location.host + location.pathname+staffDetailPage+displayName.replace(' ', '-')+'('+uuid+')">' + displayName + '</a>');
                    } else {
                        template = template.replace(/###displayName###/g, displayName);
                    }
                    var title, oname = '', organisationName = '', phone = '', roomNumber = '', homePage = '', email = '';
                    
                    if(aData.organisationId) {
                        for (i=0; i<aData.organisationId.length; i++) {
                            if(affiliation) affiliation += '<br />';
                            if(aData.title) {
                                if(aData.title[i]) {
                                    affiliation += titleCase(aData.title[i]);
                                }
                            }
                            if(aData.organisationName) {
                                if(aData.organisationName[i]) {
                                    organisationName = aData.organisationName[i];
                                }
                            }
                            if(aData.roomNumber) {
                                if(aData.roomNumber[i]) {
                                    organisationName += ' (' + lth_solr_messages.room + ' ' + aData.roomNumber[i] + ')';
                                }
                            }
                            if(aData.phone) {
                                if(aData.phone[i] && aData.phone[i] != 'NULL') {
                                    phone = addBreak(aData.phone[i]);
                                    if(phone) phone = phone.replace('+4646222', '+46 46 222 ').replace(/(.{2}$)/, ' $1');
                                }
                            }
                            //console.log(phone + ';' + displayName);

                            if(aData.mobile) {
                                if(aData.mobile[i] && aData.mobile[i] !== 'NULL') {
                                    phone += addBreak('+46 ' + aData.mobile[i].replace(/ /g, '').replace('+46','').replace(/(\d{2})(\d{3})(\d{2})(\d{2})/, "$1 $2 $3 $4"));
                                }
                            }
                        }
                    }
                    
                    if(aData.email) email = aData.email[0];
                    
                    template = template.replace(/###email###/g, email);
                    
                    if(organisationName) {
                        affiliation += addComma(organisationName);
                    }
                    if(phone) {
                        affiliation += phone;
                    }
                    template = template.replace('###affiliation###', affiliation);

                    $('#lthsolr_staff_container').append(template);

                    indexCounter++;
                    tableCounter++;

                    if(!mobileCheck()) {
                        if(isEven(ii) && uuid) {
                            thisHeight = $('#'+uuid).height();
                            lastHeight = thisHeight;
                            lastId = uuid;
                        } else if(uuid && lastId) {
                            if($('#'+uuid).height() > lastHeight) {
                                $('#'+lastId).height($('#'+uuid).height());
                            } else {
                                $('#'+uuid).height(lastHeight);
                            }
                        }
                    }
                    ii++;
                });
                $('#lthsolr_people_header').html('<span class="lth_solr_search_header"><h2>' + lth_solr_messages.people + '</h2></span><span>1' + '-' + maxLength(startPeople,tableLength,d.peopleNumFound) + ' ' + lth_solr_messages.of + ' '  + d.peopleNumFound + '</span>');

                if((parseInt(startPeople) + parseInt(tableLength)) < d.peopleNumFound) {
                    $('#lthsolr_staff_container').append('<div style="height:30px;width:100%;clear:both;">\n\
                        <button class="btn btn-default btn-lg btn-block" style="height:30px;" onclick="$(\'#lth_solr_no_items\').val(' + d.peopleNumFound + ');\n\
                        $(this).parent().remove();\n\
                        searchLong(\'' + term + '\',' + (parseInt(startPeople) + parseInt(tableLength)) + ',0,0,\'people\');">' + lth_solr_messages.show_more + ' ' + lth_solr_messages.people +
                        ' <span class="glyphicon glyphicon-chevron-down"></span></button></div>');
                }
            } else if(more==0) {
                $('#lthsolr_people_header').html('<span class="lth_solr_search_header"><h2>' + lth_solr_messages.people + '</h2></span>');
                $('#lthsolr_staff_container').html('<div>' + lth_solr_messages.No + ' ' + lth_solr_messages.hits + ' ' + 
                        lth_solr_messages.on + ' <b>' + term + '</b>.</div>');
                //$('#lthsolr_staff_container').parent().hide();
            }
            var endI;
            
            //Pages and documents******************************************************************************************************************//
            $('#lthsolr_pages_container').find('.loader').remove();

            var localize = 0;
            if(webSearchScope==='global' || more==='global') {
                localize = 1;
            }

            if(d.pageData[localize].hits > 0) {
                
                
                var i=1;
                var indexCounter = 1+startPages;
                var obj = d.pageData[localize].search_result;
                for (var key in obj) {
                    template = $('#solrPagesTemplate').html();
                    
                    id = '';
                    title = '';
                    teaser = '';
                    url = '';
                    link = '';
                    id = 'lu_'+i;
                    label = '';
                    label_sv = '';
                    label_en = '';
                    
                    if (obj.hasOwnProperty(key)) {
                        
                        var val = obj[key];
                        if(val.label_sv) {
                            label_sv = val.label_sv;
                        } 
                        if(val.label_en) {
                            label_en = val.label_en;
                        } 
                        if(val.label) {
                            label = val.label;
                        }
                        if(syslang==='sv') {
                            if(label_sv) {
                                title = label_sv;
                            } else if(label_en) {
                                title = label_en;
                            } else {
                                title = label;
                            }
                        } else {
                            if(label_en) {
                                title = label_en;
                            } else if(label_sv) {
                                title = label_sv;
                            } else {
                                title = label;
                            }
                        }
                        
                        if(val.teaser_sv) {
                            teaser = val.teaser_sv;
                        }
                        if(val.teaser_en) {
                            teaser = val.teaser_en;
                        }
                        url = val.url;
                        template = template.replace('###id###', id);
                        template = template.replace('###title###', splitString(title+'',30));
                        template = template.replace('###teaser###', teaser);
                        template = template.replace('###url###', url);
                        template = template.replace('###link###', maxCharLength(url,60));
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
                    }
                }
                
                //Svar filter = $(d.pageData).filter('.filter-wrapper').html();
                /*$(d.pageData).filter('.hit').each(function( index ) {
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
                    template = template.replace('###title###', splitString(title+'',30));
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
                });*/

                var pagesHeader = '<span class="lth_solr_search_header"><h2>'+ lth_solr_messages.webpages + '</h2></span><span class="lth_solr_number">1-';
                if($('.lth_solr_hide').length > 0) {
                    pagesHeader += endI;
                } else {
                    pagesHeader += maxLength(startPages,20,d.pageData[localize].hits);
                }
                pagesHeader += '</span> ' + lth_solr_messages.of + ' '  + d.pageData[localize].hits;
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
                
                if((parseInt(startPages) + parseInt(20)) < d.pageData[localize].hits) {
                    next = '<li style="margin-top:20px;">\n\
                        <button class="btn btn-default btn-lg btn-block" style="height:30px;" href="javascript:" \n\
                        onclick="$(\'#lth_solr_no_items\').val(' + d.pageData[localize].hits + '); \n\
                        $(\'#lthsolr_pages_container li:last\').remove();\n\
                        searchLong(\'' + term + '\',0,' + (parseInt(startPages) + parseInt(20)) + ',0,\''+ webSearchScope + '\');">\n\
                        ' + lth_solr_messages.show_more + ' ' + lth_solr_messages.webpages + ' <span class="glyphicon glyphicon-chevron-down"></span></button></li>';
                }
                if($('.lth_solr_hide').length > 0) {
                    $('#lthsolr_pages_container').append('<li style="margin-top:20px;">\n\
                        <button class="btn btn-default btn-lg btn-block" style="height:30px;" href="javascript:" onclick="$(\'.lth_solr_hide\').show(300);\n\
                        $(\'li\').removeClass(\'lth_solr_hide\');\n\
                        $(\'.lth_solr_number\').text(\'1-20\');\n\
                        $(\'#lthsolr_pages_container li:last\').replaceWith(next);">\n\
                        ' + lth_solr_messages.show_more + ' ' + lth_solr_messages.webpages + ' <span class="glyphicon glyphicon-chevron-down"></span></button></li>');
                } else if((parseInt(startPages) + parseInt(20)) < d.pageData[localize].hits ) {
                    $('#lthsolr_pages_container').append(next);
                }
            } else if(more==0) {
                $('#lthsolr_pages_header').html('<span class="lth_solr_search_header"><h2>'+ lth_solr_messages.webpages + '</h2></span>');
                $('#lthsolr_pages_container').html('<div>' + lth_solr_messages.No + ' ' + lth_solr_messages.hits + ' ' + 
                        lth_solr_messages.on + ' <b>' + term + '</b>.</div>');
                //$('#lthsolr_pages_container').parent().hide();
            }
                
            // COURSES**************************************************************************************************************//
            /*var tableCounter = 3;
            var indexCounter = 1+startCourses;
            $('#lthsolr_courses_container tbody tr:first').remove();
            var courseCode, credit, homepage;

            if(d.optionalData.length > 0) {
                $('.lth_solr_res').append('<li>Courses</li>');
                $.each( d.optionalData, function( key, aData ) {
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
                    
                    if(tableCounter === 3) {
                        $('#lthsolr_courses_container tbody').append('<tr></tr>');
                        tableCounter = 0
                    }
                    $('#lthsolr_courses_container tbody tr:last').append(template);
                    tableCounter++;
                    indexCounter++;
                });

                $('#lthsolr_courses_header').html('<span class="lth_solr_search_header"><h3>'+ lth_solr_messages.courses + '</h3></span><span>1-' + maxLength(startCourses,tableLength,d.courseNumFound) + ' ' + lth_solr_messages.of + ' '  + d.courseNumFound + '</span>');
                
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
            }*/
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


function listPublications(tableStart, facet, query, sorting, more, lastGroupValue, action)
{
    
    var syslang = $('html').attr('lang');
    var scope = $('#lth_solr_scope').val();
    var tableLength = $('#lth_solr_no_items').val();
    var keyword = $('#lth_solr_keyword').val();
    var pageTitle = $('#lth_solr_pagetitle').val();
    var publicationCategories = $('#lth_solr_publicationCategories').val();
    var publicationCategoriesSwitch = $('#lth_solr_publicationCategoriesSwitch').val();
    var display = $('#lth_solr_display').val();
    var displayLayout = $('#lth_solr_displayLayout').val();
    var displayFromSimpleList = $('#lth_solr_displayFromSimpleList').val();
    var inputFacet = facet;
    var i = 0;
    var maxClass, count, facetHeader, more, content, numberOfPages, publicationDate, journalTitle, title, placeOfPublication, authorName, openAccessPermission;
    var id, publisher, hostPublicationTitle, volume, pages, articleNumber, bibliographicalNote;
    var electronicIsbn, electronicVersionAccessType, electronicVersionDoi, electronicVersionFileName, electronicVersionFileURL, electronicVersionLicenseType;
    var electronicVersion, electronicVersionLink, electronicVersionMimeType, electronicVersionSize, electronicVersionTitle, electronicVersionVersionType;
    var exportArray = ["articleNumber","authorName","documentTitle","documentLimitedVisibility","documentMimeType","documentSize",
            "documentUrl","hostPublicationTitle","id","journalTitle","journalNumber","numberOfPages","openAccessPermission","pages","publicationType","publicationDateYear",
            "publicationDateMonth","publicationDateDay","placeOfPublication","publisher","volume"];
        
    if(publicationCategoriesSwitch === 'all') {
        publicationCategories = '';
    } else if(publicationCategoriesSwitch === 'free' || publicationCategoriesSwitch === 'campus') {
        publicationCategories = publicationCategoriesSwitch;
    }

    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: action,
            displayLayout: displayLayout,
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
                $('#lthsolr_publications_container div').remove().append('<img class="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
            } else {
                $('.lthsolr_more').html('').addClass('loader');
            }
            $('#lth_solr_facet_container ul li ul li').remove();
        },
        success: function(d) {
            $('.loader').remove();
            $('.lthsolr_more').remove();
            if(pageTitle) {
                pageTitle = ' ' + titleCase(pageTitle) + ' ';
            } else {
                pageTitle='';
            }
            if(!mobileCheck()) $('#lthsolr_publications_header').html(lth_solr_messages.publications);
            $('#lthsolr_publications_header').append(pageTitle);
            if(d.data) {
                if(d.facet && !displayFromSimpleList) {
                    if(mobileCheck()) {
                        if($('#lth_solr_facet_container').length == 0) {
                            $('#lthsolr_publications_container').append('<div id="lth_solr_facet_container">'+
                                '<div style="margin-top:15px;" class="input-group">'+
                                    '<span class="input-group-addon" id="basic-addon1"><i class="fa fa-search fa-sm slsGray20"></i></span>'+
                                    //'<i class="fa fa-search fa-lg slsGray50"></i>' +
                                    '<input type="text" style="font-size:12px;" class="form-control" id="lthsolr_publications_filter" placeholder="" value="" />'+
                                    '</div>'+
                                    '<ul style=""><li><i class="fa fa-angle-right fa-sm slsGray20"></i><a href="javascript:" onclick="$(\'.maxlist-all\').toggle(500);">'+lth_solr_messages.moreFilteringOptions+'</a><ul class="maxlist-all"><li></li></ul></li></ul></div>');
                            $('#lthsolr_publications_filter').keyup(function() {
                                listPublications(0, getFacets(), $(this).val().trim(), $("#lthsolr_sort").val(), 0,'', action);
                            });
                        }
                    } else {
                        if($('#lth_solr_facet_container').length == 0) {
                            $('#content_navigation,#subnavigation').append('<div id="lth_solr_facet_container">'+
                                    '<b>' + lth_solr_messages.filterSearchResult+'</b>'+
                                    '<div style="margin-top:15px;" class="input-group">'+
                                    '<span class="input-group-addon" id="basic-addon1"><i class="fa fa-search fa-sm slsGray20"></i></span>'+
                                    //'<i class="fa fa-search fa-lg slsGray50"></i>' +
                                    '<input type="text" style="font-size:12px;height:31px;" class="form-control" id="lthsolr_publications_filter" placeholder="" value="" />'+
                                    '</div>'+
                                    '<ul style="border-top:1px #dedede solid;margin-top:15px;padding-top:7px;"><li><ul><li></li></ul></li></ul></div>');
                            $('#lthsolr_publications_filter').keyup(function() {
                                listPublications(0, getFacets(), $(this).val().trim(), $("#lthsolr_sort").val(), 0,'', action);
                            });
                        }
                    }

                    $.each( d.facet, function( key, value ) {
                        maxClass='';
                        more='';
                        content = '';
                        i=0;
                        $.each( value, function( key1, value1 ) {
                            if(i > 4) {
                                maxClass = ' class="maxlist-hidden"';
                                more = '<li class="maxlist-more"><i class="fa fa-chevron-right"></i><a href="#">' + lth_solr_messages.more + '</a></li>';
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
                                content += '<li' + maxClass + ' style=""><label>';
                                content += facet.capitalize().replace(/_/g, ' ') + '&nbsp;[' + count + '] ';
                                content += '<input type="checkbox" class="lth_solr_facet item-list" name="lth_solr_facet" value="' + key + '###' + facet + '"' + facetCheck + '>';
                                content += '</label></li>';
                            }
                            i++;
                        });

                        $('#lth_solr_facet_container ul > li > ul').append('<li style=""><b>'+facetHeader+'</b></li>' + content + more + '');
                        i=0;
                    });

                    createFacetClick('listPublications', sorting, action);
                }

                var publicationDetailPage = 'visa';
                if(syslang=='en') {
                    publicationDetailPage = 'show';
                }

                $.each( d.data, function( key, aData ) {
                    if(sorting==='publicationYear' && display==='publications') {
                        if(lastGroupValue!=aData.publicationDateYear) {
                            $('#lthsolr_publications_container').append('<div class="lthsolr_publication_row" style="margin-top:0px;padding-top:15px;font-weight:bold;">'+aData.publicationDateYear+'</div>');
                        }
                    }
                    if(sorting==='publicationType' && display==='publications') {
                        if(lastGroupValue!=aData.publicationType) {
                            $('#lthsolr_publications_container').append('<div class="lthsolr_publication_row" style="margin-top:0px;">'+aData.publicationType+'</div>');
                        }
                    }
                    var template = $('#solrPublicationTemplate').html();
                    
                    articleNumber = '';
                    authorName = '';
                    bibliographicalNote = ''.
                    documentTitle = '';
                    electronicIsbn = '';
                    electronicVersion = '';
                    electronicVersionAccessType = '';
                    electronicVersionDoi = '';
                    electronicVersionFileName = '';
                    electronicVersionFileURL = '';
                    electronicVersionLicenseType = '';
                    electronicVersionLink = '';
                    electronicVersionMimeType = '';
                    electronicVersionSize = '';
                    electronicVersionTitle = '';
                    electronicVersionVersionType = '';
                    hostPublicationTitle = '';
                    journalTitle = '';
                    numberOfPages = '';
                    openAccessPermission = '';
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
                    if(displayFromSimpleList) {
                        path = displayFromSimpleList + publicationDetailPage;
                    } else if(window.location.href.indexOf('(') > 0) {
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
                    
                    //openAccessPermission
                    if(aData.openAccessPermission) openAccessPermission = aData.openAccessPermission;

                    //electronicVersionDoi
                    if(aData.electronicVersionDoi) electronicVersionDoi = aData.electronicVersionDoi;
                            
                    //electronicVersionFileName
                    if(aData.electronicVersionFileName) electronicVersionFileName = aData.electronicVersionFileName;
                    
                    //electronicVersionFileURL
                    if(aData.electronicVersionFileURL) electronicVersionFileURL = aData.electronicVersionFileURL;
                    
                    //electronicVersionLicenseType
                    if(aData.electronicVersionLicenseType) electronicVersionLicenseType = aData.electronicVersionLicenseType;
                    
                    //electronicVersionLink
                    if(aData.electronicVersionLink) electronicVersionLink = aData.electronicVersionLink;
                    
                    //electronicVersionMimeType
                    if(aData.electronicVersionMimeType) electronicVersionMimeType = aData.electronicVersionMimeType;
                    
                    //electronicVersionSize
                    if(aData.electronicVersionSize) electronicVersionSize = aData.electronicVersionSize;
                    
                    //electronicVersionTitle
                    if(aData.electronicVersionTitle) electronicVersionTitle = aData.electronicVersionTitle;
                    
                    //electronicVersionVersionType
                    if(aData.electronicVersionVersionType) electronicVersionVersionType = aData.electronicVersionVersionType;
        
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
                    
                    //###bibliographicalNote
                    if(aData.bibliographicalNote) {
                        bibliographicalNote = aData.bibliographicalNote;
                    }

                    template = template.replace('###articleNumber###', articleNumber);
                    template = template.replace('###authorName###', authorName);
                    template = template.replace('###bibliographicalNote###', bibliographicalNote);
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

                    if(electronicVersionFileURL || openAccessPermission) {
                        if(electronicVersionFileURL) {
                            electronicVersion = '<i class="fa fa-paperclip"></i>';
                        }
                        if(openAccessPermission) {
                            if(openAccessPermission==='Öppen' || openAccessPermission==='Open') {
                                electronicVersion += '<i class="fa fa-unlock"></i>';
                            }
                        }
                        $('#'+id).append('<div class="lthsolr_electronicVersion">'+electronicVersion+'</div>');
                    }
                        
                    if(sorting==='publicationYear') {
                        lastGroupValue = aData.publicationDateYear;
                    }
                    if(sorting==='publicationType') {
                        lastGroupValue = aData.publicationType;
                    }
                });
                
                $('.lthsolr_loader').remove();
                if(display==='publications') {
                    var sortButton = '<select id="lthsolr_sort" style="direction: rtl;"></select>';
                    $('#lthsolr_publications_sort').html(sortButton);
                    var sortoptions = ["publicationYear,"+lth_solr_messages.publicationYear+",&#xf161;","publicationType,"+lth_solr_messages.type+",&#xf160;","documentTitle,"+lth_solr_messages.title+",&#xf160;","authorName,"+lth_solr_messages.authorLastName+",&#xf160;"];
                    $(sortoptions).each(function(index, value){
                        var option = '<option value="' + value.toString().split(',')[0] + '"';
                        if(sorting == value.toString().split(',')[0]) {
                            option += ' selected="selected"';
                        }
                        option += '>' + value.toString().split(',')[1] + ' ' + value.toString().split(',')[2] + '</option>';
                        $('#lthsolr_sort').append(option);
                    });
                    $('#lthsolr_publications_header').append(' (1-' + maxLength(parseInt(tableStart),parseInt(tableLength),parseInt(d.numFound)) + ' ' + lth_solr_messages.of + ' ' + d.numFound + ')');
                    
                    if((parseInt(tableStart) + parseInt(tableLength)) < d.numFound) {
                        var tempMore = '<div style="margin-top:20px;" class="lthsolr_more"><button style="height:40px;" class="btn btn-default btn-lg btn-block" ' +
                            'onclick="listPublications(' + (parseInt(tableStart) + parseInt(tableLength)) + ',getFacets(),\'\',\'' + sorting + '\',\'more\',\''+
                                lastGroupValue + '\',\'' + action + '\');">' + lth_solr_messages.show_more + ' ' + lth_solr_messages.publications + 
                                ' <span class="glyphicon glyphicon-chevron-down"></span></button>';

                        tempMore += '</div>';
                        $('#lthsolr_publications_container').append(tempMore);
                    }
                    
                    if($('#lth_solr_lu').val() === "yes" && $('.fa-download').length < 1) {
                        if(mobileCheck()) {
                            /*$('#lthsolr_publications_container').append('<div><button style="height:40px;" class="btn btn-default btn-lg btn-block">' + 
                                    lth_solr_messages.export + 
                                    '<i style="cursor:pointer;" class="fa fa-download fa-lg slsGray50"></i>' +
                                    '</button></div>');
                            */
                        } else {
                            $('#lth_solr_facet_container').append('<div style="margin-top:15px;border-top:1px #dedede solid;padding-top:7px;"><b>' + lth_solr_messages.export + '</b></div><a class="fa-downloada" href="javascript:"><i style="cursor:pointer;" class="fa fa-download fa-lg slsGray50"></i></a>');
                        }
                        $('.fa-downloada').click(function() {
                            if($('#exportModal .modal-body .checkbox').length === 0) {
                                for (var i=0; i<exportArray.length; i++) {
                                    $('#exportModal .modal-body').append('<div class="checkbox"><label><input type="checkbox" name="exportField" value="'+exportArray[i]+'">'+exportArray[i]+
                                            '</label></div>');
                                }
                                $('.modal-body').prepend('<div class="checkbox"><label><input type="checkbox" id="select_all" /></label><i class="fa fa-check"></i></div>');
                                $('.modal-body').append('<button id="exportButton" type="button" class="btn btn-default">Export</button>');
                                $('.modal-body').wrap('<form></form>');
                                $('#exportModal #exportButton').click(function(){
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
                }
            } else if(!query) {
                $('.lth_solr_filter_container').next().remove();
                $('.lth_solr_filter_container').remove();
            }
            
            $("#lthsolr_sort").change(function(){
                listPublications(0,inputFacet,query,$( this ).val(),0,'',action);
            });
            
            toggleFacets();
        }
    });
}
 

function listTagCloud()
{
    var syslang = $('html').attr('lang');
    var scope = $('#lth_solr_scope').val();
    /*var publicationDetailPage = 'publikationer';
    if(syslang=='en') {
        publicationDetailPage = 'publications';
    }*/
    var path = window.location.pathname;
    
    $.ajax({
        type : "POST",
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'listTagCloud',
            pageid: $('body').attr('id'),
            tableLength: $('#lth_solr_no_items').val(),
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
            $('#lth_solr_facet_container ul li ul li').remove();
        },
        success: function(d) {
            $('.loader').remove();
            $('.lthsolr_more').remove();
            if(d.data) {
                if(d.facet) {
                    if(mobileCheck()) {
                        if($('#lth_solr_facet_container').length == 0) {
                            $('#lthsolr_publications_container').append('<div id="lth_solr_facet_container">'+
                                '<div style="margin-top:15px;" class="input-group">'+
                                    '<span class="input-group-addon" id="basic-addon1"><i class="fa fa-search fa-sm slsGray20"></i></span>'+
                                    //'<i class="fa fa-search fa-lg slsGray50"></i>' +
                                    '<input type="text" style="font-size:12px;" class="form-control" id="lthsolr_publications_filter" placeholder="" value="" />'+
                                    '</div>'+
                                    '<ul style=""><li><i class="fa fa-angle-right fa-sm slsGray20"></i><a href="javascript:" onclick="$(\'.maxlist-all\').toggle(500);">'+lth_solr_messages.moreFilteringOptions+'</a><ul class="maxlist-all"><li></li></ul></li></ul></div>');
                            $('#lthsolr_publications_filter').keyup(function() {
                                listStudentPapers(0, getFacets(), $(this).val().trim(), 0);
                            });
                        }
                    } else {
                        if($('#lth_solr_facet_container').length == 0) {
                            $('#content_navigation,#subnavigation').append('<div id="lth_solr_facet_container">'+
                                    '<b>' + lth_solr_messages.filterSearchResult+'</b>'+
                                    '<div style="margin-top:15px;" class="input-group">'+
                                    '<span class="input-group-addon" id="basic-addon1"><i class="fa fa-search fa-sm slsGray20"></i></span>'+
                                    //'<i class="fa fa-search fa-lg slsGray50"></i>' +
                                    '<input type="text" style="font-size:12px;height:31px;" class="form-control" id="lthsolr_publications_filter" placeholder="" value="" />'+
                                    '</div>'+
                                    '<ul style="border-top:1px #dedede solid;margin-top:15px;padding-top:7px;"><li><ul><li></li></ul></li></ul></div>');
                            $('#lthsolr_publications_filter').keyup(function() {
                                listStudentPapers(0, getFacets(), $(this).val().trim(), 0);
                            });
                        }
                    }

                    $.each( d.facet, function( key, value ) {
                        maxClass='';
                        more='';
                        content = '';
                        i=0;
                        $.each( value, function( key1, value1 ) {
                            if(i > 4) {
                                maxClass = ' class="maxlist-hidden"';
                                more = '<li class="maxlist-more"><i class="fa fa-chevron-right"></i><a href="#">' + lth_solr_messages.more + '</a></li>';
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
                                content += '<li' + maxClass + ' style=""><label>';
                                content += facet.capitalize().replace(/_/g, ' ') + '&nbsp;[' + count + '] ';
                                content += '<input type="checkbox" class="lth_solr_facet item-list" name="lth_solr_facet" value="' + key + '###' + facet + '"' + facetCheck + '>';
                                content += '</label></li>';
                            }
                            i++;
                        });

                        $('#lth_solr_facet_container ul > li > ul').append('<li style=""><b>'+facetHeader+'</b></li>' + content + more + '');
                        i=0;
                    });

                    createFacetClick('listStudentPapers','','');
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


function showStudentPaperNovo()
{
    var sysLang = $('html').attr('lang');
    var abstract,documentTitle,authors,organisations,externalOrganisations,publicationType,language,publicationDateYear,
        keywords,documentUrl,supervisorName,organisationSourceId,bibtex;
    
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'showStudentPaperNovo',
            dataSettings: {
                pageid : $('body').attr('id'),
                scope : $('#lth_solr_scope').val(),
                sysLang : sysLang,
            },
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: 'json',
        beforeSend: function () {
            $('#lthsolr_show_studentpapercontainer').append(getSpinner(sysLang));
        },
        success: function(d) {
            $('.spinner').remove();
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
            
            $('#page_title h1, #pageTitle').text(documentTitle).css('max-width','650px');
            
            var organisations = '', abstractShort = '', abstractFull = '';
            var path = window.location.href.split('(').shift().split('/');
            path.pop();
            path = path.join('/');
                
            if(d.data) {               
                var template = $('#solrTemplate').html();
                if(abstract) {
                    if(abstract.length > 200) {
                        abstractShort = abstract.substr(0,200);
                        abstractFull = abstract.substr(200, abstract.length);
                    } else {
                        abstractShort = abstract;
                        abstractFull = '';
                    }
                }
                template = template.replace('###abstractShort###', abstractShort);
                template = template.replace('###abstractFull###', abstractFull);
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
                $('#lthsolr_show_studentpapercontainer').html(template);
                
                if(abstract) {
                    $('.expand-closed').click(function() {
                        $('.expand-content-body, .expand-open').show(200);
                        $(this).toggle();
                    });
                    $('.expand-open').click(function() {
                        $('.expand-content-body').hide(200);
                        $('.expand-closed').toggle();
                        $(this).toggle();
                    });
                }
            }
            
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
    var abstract='',additionalLink = '',authorId='',authorExternal='',authorName='',authorOrganisationId,authorReverseName,authorReverseNameShort,bibtex,cite,doi,edition,electronicIsbns;
    var electronicVersionAccessType, electronicVersionDoi, electronicVersionFileName, electronicVersionFileURL, electronicVersionLicenseType;
    var electronicVersion, electronicVersionLink, electronicVersionMimeType, electronicVersionSize, electronicVersionTitle, electronicVersionVersionType, endDate;
    var externalOrganisations,event,eventName,eventLink,eventType,eventCity,eventCountry,hostPublicationTitle,id,issn,journalNumber,journalTitle,keywords_uka,keywords_user;
    var language,pages,numberOfPages,openAccessPermission;
    var organisationName,organisationSourceId,organisationId,peerReview,placeOfPublication;
    var printIsbns,publicationStatus,publicationDateYear,publicationDateMonth,publicationDateDay,publicationType,publicationTypeUri,publisher,startDate,supervisors;
    var title,volume;
    var scope = $('#lth_solr_scope').val();
    scope = decodeURIComponent(scope);
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'showPublication',
            term : $('#lth_solr_uuid').val(),
            scope : $('#lth_solr_scope').val(),
            syslang : syslang,
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: 'json',
        beforeSend: function () {
            $('#lth_solr_container').detach().insertAfter("#page_title, article h1");
            //var solrId = $('#lth_solr_container').parent().attr('id');
            //$('#'+solrId).parent().find('> div:not(#'+solrId+')').remove();
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
                
                //additionalLink
                if(d.data.additionalLink) {
                    additionalLink = '<p><b>' + lth_solr_messages.additionalLink + '</b>';
                    for(var i = 0; i < d.data.additionalLink.length; i++) {
                        additionalLink += '<br /><a href="' + d.data.additionalLink[i] + '">' + d.data.additionalLink[i] + '</a>';
                    }
                    additionalLink += '</p>';
                }

                //electronicVersionAccessType
                if(d.data.electronicVersionAccessType) electronicVersionAccessType = d.data.electronicVersionAccessType;

                //electronicVersionDoi
                if(d.data.electronicVersionDoi) electronicVersionDoi = d.data.electronicVersionDoi;

                //electronicVersionFileName
                if(d.data.electronicVersionFileName) electronicVersionFileName = d.data.electronicVersionFileName;

                //electronicVersionFileURL
                if(d.data.electronicVersionFileURL) electronicVersionFileURL = d.data.electronicVersionFileURL;

                //electronicVersionLicenseType
                if(d.data.electronicVersionLicenseType) electronicVersionLicenseType = d.data.electronicVersionLicenseType;

                //electronicVersionLink
                if(d.data.electronicVersionLink) electronicVersionLink = d.data.electronicVersionLink;

                //electronicVersionMimeType
                if(d.data.electronicVersionMimeType) electronicVersionMimeType = d.data.electronicVersionMimeType;

                //electronicVersionSize
                if(d.data.electronicVersionSize) electronicVersionSize = d.data.electronicVersionSize;

                //electronicVersionTitle
                if(d.data.electronicVersionTitle) electronicVersionTitle = d.data.electronicVersionTitle;

                //electronicVersionVersionType
                if(d.data.electronicVersionVersionType) electronicVersionVersionType = d.data.electronicVersionVersionType;
                
                authorId = d.data.authorId;
                authorExternal = d.data.authorExternal;
                authorName = d.data.authorName;
                authorOrganisationId = d.data.authorOrganisationId;
                authorReverseName = d.data.authorReverseName;
                authorReverseNameShort = d.data.authorReverseNameShort;
                bibtex = d.data.bibtex;
                cite = d.data.cite;
                doi = d.data.doi;
                edition = d.data.edition;
                electronicIsbns = d.data.electronicIsbns;
                endDate = d.data.endDate;
                eventName = d.data.eventName;
                eventLink = d.data.eventLink;
                eventType = d.data.eventType;
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
                openAccessPermission = d.data.openAccessPermission;
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
                startDate = d.data.startDate;
                supervisors = d.data.supervisors;
                title = d.data.title;
                volume = d.data.volume;
                
                var organisations = '';
                var path = window.location.href.split('(').shift().split('/');
                path.pop();
                path = path.join('/');
                
                var authors = '';
                if(authorName) {
                    //console.log(authorName+';'+authorId);
                    //var authorNameArray = authorName.split(',');
                    //var authorIdArray = authorId.split(',');
                    var authorExternalArray = authorExternal.split(',');
                    for(var i = 0; i < authorName.length; i++) {
                        if(authors) {
                            authors += ', ';
                        }
                        if(authorId[i] && authorId[i] !== '####' && authorExternalArray[i]==0) {
                            authors += '<a href="' + detailLink + authorName[i].replace(' ','-') + '(' + authorId[i] + ')(author)">' + authorName[i] + '</a>';
                        } else {
                            authors += authorName[i];
                        }
                    }
                }
                
                if(eventName) {
                    event = eventName;
                }
                if(eventCity) {
                    event += addComma(eventCity);
                }
                if(eventCountry) {
                    event += addComma(eventCountry);
                }
                if(startDate && startDate !== '1970-01-01T00:00:00Z') {
                    event += '<br />' + startDate.substr(0,10);
                }
                if(endDate && endDate !== '1970-01-01T00:00:00Z') {
                    event +=  ' -- ' + endDate.substr(0,10);
                }
                
                for(var i = 0; i < organisationName.length; i++) {
                    if(organisations) {
                        organisations += ', ';
                    }
                    if(organisationId[i] && organisationId[i] !== '####') {
                        organisations += '<a href="' + detailLink + organisationName[i] + '(' + organisationId[i] + ')(department)">' + organisationName[i] + '</a>';
                    } else {
                        organisations += organisationName[i];
                    }
                }
                
                /*if(organisationId && organisationId !== '####') {
                   organisations = '<a href="' + detailLink + organisationName + '('+ organisationId + ')(department)">' + organisationName + '</a>';
                } else {
                    organisations = organisationName;
                }*/
                
                if(keywords_user) {
                    for(var i = 0; i < keywords_user.length; i++) {
                        //console.log(keywords_user[i]);
                    }
                }
                
                electronicVersion = '';
                var ii=0;
                //electronic version doi
                if(electronicVersionDoi) {
                    for(var i = 0; i < electronicVersionDoi.length; i++) {
                        if(electronicVersionDoi[i]) {
                            if(ii===0) electronicVersion += '<p><b>DOI</b><br />';
                            electronicVersion += checkOpen(electronicVersionVersionType[i], electronicVersionAccessType[i]);
                            electronicVersion += '<a href="'+electronicVersionDoi[i]+'">'+electronicVersionDoi[i]+'</a> (<i>' + electronicVersionVersionType[i] + '</i>)</p>';
                            ii++;
                        }
                    }
                }
                ii=0;
                //electronic version dok
                if(electronicVersionFileName) {
                    for(var i = 0; i < electronicVersionFileName.length; i++) {
                        if(electronicVersionFileURL[i]) {
                            if(ii===0) electronicVersion += '<p><b>Dokument</b><br />';
                            checkOpen(electronicVersionVersionType[i], electronicVersionAccessType[i]);
                            electronicVersion += '<a href="'+electronicVersionFileURL[i]+'">'+electronicVersionFileName[i]+'</a> (<i>' + electronicVersionVersionType[i] + addComma(formatBytes(electronicVersionSize[i],0)) + addComma(electronicVersionMimeType[i]) + '</i>)</p>';                           
                            ii++;
                        }
                    }
                    
                }
                ii=0;
                //electronic version link
                if(electronicVersionLink) {
                    for(var i = 0; i < electronicVersionLink.length; i++) {
                        if(electronicVersionLink[i]) {
                            if(ii===0) electronicVersion += '<p><b>Länkar</b><br />';
                            checkOpen(electronicVersionVersionType[i], electronicVersionAccessType[i]);
                            electronicVersion += '<a href="'+electronicVersionLink[i]+'">'+electronicVersionLink[i]+'</a> (<i>' + electronicVersionVersionType[i] + '</i>)</p>';
                            ii++;
                        }
                    }
                }
                
                /*if(electronicVersionLink || electronicVersionDoi || electronicVersionFileURL) {
                    
                    if(electronicVersionAccessType) {
                        if(electronicVersionAccessType.toLowerCase()==='öppen' || electronicVersionAccessType.toLowerCase()==='free') {
                            electronicVersion = '<i class="fa fa-unlock"></i>';
                        } else if(electronicVersionAccessType.toLowerCase()==='begränsad' || electronicVersionAccessType.toLowerCase()==='closed') {
                            electronicVersion = '<i class="fa fa-lock"></i>';
                        }
                    }
                    
                    if(electronicVersionLink || electronicVersionFileURL) {
                        if(!electronicVersionLink && electronicVersionFileURL) {
                            electronicVersionLink = electronicVersionFileURL;
                        }
                        electronicVersion = '<p><b>' + lth_solr_messages.documents + '</b><br>' + electronicVersion + '<a href="' + electronicVersionLink + '">' + electronicVersionLink + '</a></p>';
                    } else {
                        electronicVersion = checkData(electronicVersion + '<a href="' + electronicVersionDoi + '">' + electronicVersionDoi + '</a>',electronicVersion.doi);
                    }
                }*/

                template = template.replace('###tabOverview###', lth_solr_messages.overview);
                template = template.replace('###abstract###', checkData(abstract, lth_solr_messages.abstract));
                template = template.replace('###additionalLink###', additionalLink);
                template = template.replace('###electronicVersion###', electronicVersion)
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
                
                $('#page_title h1, article h1').text(d.data.title).css('max-width','650px').css('margin-bottom','18px');
                $('#page_title h1, article h1').after('<p class="type">' + d.data.publicationType + '</p>');
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


function showPublicationNovo()
{
    //var lth_solr_staffdetailpage = $('#lth_solr_staffdetailpage').val();
    //var lth_solr_projectdetailpage = $('#lth_solr_projectdetailpage').val();
    var sysLang = $('html').attr('lang');
    var abstract='',authors='',additionalLink = '',authorId='',authorExternal='',authorName='',authorOrganisationId,authorReverseName,authorReverseNameShort,bibtex,cite,detailLink,doi,edition,electronicIsbns;
    var electronicVersionAccessType, electronicVersionDoi, electronicVersionFileName, electronicVersionFileURL, electronicVersionLicenseType;
    var electronicVersion, electronicVersionLink, electronicVersionMimeType, electronicVersionSize, electronicVersionTitle, electronicVersionVersionType, endDate;
    var externalOrganisations,event,eventName,eventLink,eventType,eventCity,eventCountry,hostPublicationTitle,id,issn,journalNumber,journalTitle,keywords_uka,keywords_user;
    var language,pages,numberOfPages,openAccessPermission;
    var organisationName,organisationSourceId,organisationId,peerReview,placeOfPublication;
    var printIsbns,publicationStatus,publicationDateYear,publicationDateMonth,publicationDateDay,publicationType,publicationTypeUri,publisher,startDate,supervisors;
    var title,volume;
    var scope = $('#lth_solr_scope').val();
    scope = decodeURIComponent(scope);
    
    $('.breadcrumb-item:last').html('<a href="../../">' + $('.breadcrumb-item:last').text() + '</a>');
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'showPublicationNovo',
            dataSettings: {
                scope : $('#lth_solr_scope').val(),
                syslang : sysLang,
            },
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: 'json',
        beforeSend: function () {
            $('#lthsolr_organisation_container > div > section').empty();
            $('#lthsolr_organisation_container > div > section').append(getSpinner(sysLang));
        },
        success: function(d) {
            $('.spinner').remove();
            if(d.data) {
                var template = $('#solrTemplate').html();            
                
                
                doi = d.data.doi;
                edition = d.data.edition;
                externalOrganisations = d.data.externalOrganisations;
                id = d.data.id;
                
                journalNumber = d.data.journalNumber;
                journalTitle = d.data.journalTitle;
                
                openAccessPermission = d.data.openAccessPermission;
                
                peerReview = d.data.peerReview;
                               
                publicationType = d.data.publicationType;
                publicationTypeUri = d.data.publicationTypeUri;
                
                supervisors = d.data.supervisors;
                title = d.data.title;
                
                var path = window.location.href.split('(').shift().split('/');
                path.pop();
                path = path.join('/');
                            
                electronicVersion = '';
                var ii=0;
                
                if(electronicVersionLink) {
                    for(var i = 0; i < electronicVersionLink.length; i++) {
                        if(electronicVersionLink[i]) {
                            if(ii===0) electronicVersion += '<p><b>Länkar</b><br />';
                            checkOpen(electronicVersionVersionType[i], electronicVersionAccessType[i]);
                            electronicVersion += '<a href="'+electronicVersionLink[i]+'">'+electronicVersionLink[i]+'</a> (<i>' + electronicVersionVersionType[i] + '</i>)</p>';
                            ii++;
                        }
                    }
                }

                /*template = template.replace('###abstract###', abstract);
                template = template.replace('###additionalLink###', additionalLink);
                template = template.replace('###electronicVersion###', electronicVersion)
                template = template.replace('###edition###', checkData(edition, lth_solr_messages.edition));
                template = template.replace('###electronicIsbns###', checkData(electronicIsbns, lth_solr_messages.electronicIsbns));
                template = template.replace('###event###', checkData(event, lth_solr_messages.event));
                template = template.replace('###externalOrganisations###', checkData(externalOrganisations, lth_solr_messages.externalOrganisations));
                template = template.replace('###journalTitle###', checkData(journalTitle, lth_solr_messages.journalTitle));
                template = template.replace('###journalNumber###', checkData(journalNumber, lth_solr_messages.journalNumber));
                template = template.replace('###keywords_uka###', checkData(keywords_uka, lth_solr_messages.keywords_uka));
                template = template.replace('###keywords_user###', checkData(keywords_user, lth_solr_messages.keywords_user));
                template = template.replace('###organisations###', checkData(organisations, lth_solr_messages.organisations));
                template = template.replace('###peerReview###', checkData(peerReview, lth_solr_messages.peerReview, sysLang));
                template = template.replace('###publisher###', checkData(publisher, lth_solr_messages.publisher));
                template = template.replace('###supervisors###', checkData(supervisors, lth_solr_messages.supervisors, sysLang));
                template = template.replace(/###title###/g, title);
                template = template.replace('###volume###', checkData(volume, lth_solr_messages.volume));*/

                //bibtex and cite
                /*template = template.replace('###bibtex###', bibtex);
                template = template.replace('###cite###', cite);*/
                
                $('#page_title h1, article h1').text(d.data.title).css('max-width','650px').css('margin-bottom','18px');
                $('#page_title h1, article h1').after('<p class="type">' + d.data.publicationType + '</p>');
                $('#lthsolr_show_publication_container').html(template);
                
                //abstract
                if(d.data.abstract) {
                    $('#lthSolrAbstract').append('<h3>' + lth_solr_messages.abstract + '</h3>')
                    $('#lthSolrAbstract').append(d.data.abstract);
                }
                //authors
                if(d.data.authorName) {
                    authorId = d.data.authorId;
                    authorExternal = d.data.authorExternal;
                    authorName = d.data.authorName;
                    authorOrganisationId = d.data.authorOrganisationId;
                    authorReverseName = d.data.authorReverseName;
                    authorReverseNameShort = d.data.authorReverseNameShort;
                    var authorExternalArray = authorExternal.split(',');
                    for(var i = 0; i < authorName.length; i++) {
                        if(authors) {
                            authors += ', ';
                        }
                        if(authorId[i] && authorId[i] !== '####' && authorExternalArray[i]==0) {
                            authors += '<a href="' + detailLink + authorName[i].replace(' ','-') + '(' + authorId[i] + ')(author)">' + authorName[i] + '</a>';
                        } else {
                            authors += authorName[i];
                        }
                    }
                    $('#lthSolrAuthors').append(authors);
                }
                //organisations
                if(d.data.organisationName) {
                    var organisations = '';
                    organisationName = d.data.organisationName;
                    organisationId = d.data.organisationId;
                    organisationSourceId = d.data.organisationSourceId;
                    for(var i = 0; i < organisationName.length; i++) {
                        if(organisations) {
                            organisations += ', ';
                        }
                        if(organisationId[i] && organisationId[i] !== '####') {
                            organisations += '<a href="' + detailLink + organisationName[i] + '(' + organisationId[i] + ')(department)">' + organisationName[i] + '</a>';
                        } else {
                            organisations += organisationName[i];
                        }
                    }
                    $('#lthSolrOrganisations').append(organisations);
                }
                //language
                if(d.data.language) {
                    $('#lthSolrPublicationFactsTable tbody').append('<tr><td>' + lth_solr_messages.language + '</td><td>' + titleCase(d.data.language) + '</td></tr>');
                }
                //hostPublicationTitle
                if(d.data.hostPublicationTitle) {
                    $('#lthSolrPublicationFactsTable tbody').append('<tr><td>' + lth_solr_messages.hostPublicationTitle + '</td><td>' + d.data.hostPublicationTitle + '</td></tr>');
                }
                //number of pages
                if(d.data.numberOfPages) {
                    $('#lthSolrPublicationFactsTable tbody').append('<tr><td>' + lth_solr_messages.numberOfPages + '</td><td>' + d.data.numberOfPages + '</td></tr>');
                }
                //placeOfPublication
                if(d.data.placeOfPublication) {
                    $('#lthSolrPublicationFactsTable tbody').append('<tr><td>' + lth_solr_messages.placeOfPublication + '</td><td>' + d.data.placeOfPublication + '</td></tr>');
                }
                //publisher;
                if(d.data.publisher) {
                    $('#lthSolrPublicationFactsTable tbody').append('<tr><td>' + lth_solr_messages.publisher + '</td><td>' + d.data.publisher + '</td></tr>');
                }
                //Date
                if(d.data.publicationDateYear) {
                    var pDate = d.data.publicationDateYear;
                    if(d.data.publicationDateMonth) {
                        pDate += '-' + addZero(d.data.publicationDateMonth);
                    }
                    if(d.data.publicationDateDay) {
                        pDate += '-' + addZero(d.data.publicationDateDay);
                    }
                    $('#lthSolrPublicationFactsTable tbody').append('<tr><td>' + lth_solr_messages.date + '</td><td>' + pDate + '</td></tr>');
                }
                //pages
                if(d.data.pages) {
                    $('#lthSolrPublicationFactsTable tbody').append('<tr><td>' + lth_solr_messages.pages + '</td><td>' + d.data.pages + '</td></tr>');
                }
                //chapter
                //Non appl.
                //isbn
                if(d.data.isbn2) {
                    for(var i = 0; i < d.data.isbn2.length; i++) {
                        if(i===0) {
                            $('#lthSolrPublicationFactsTable tbody').append('<tr><td>' + lth_solr_messages.isbn + '</td><td>' + isbnValue + '</td></tr>');
                        } else {
                            $('#lthSolrPublicationFactsTable tbody').append('<tr><td></td><td>' + isbnValue + '</td></tr>');
                        }
                    }
                }
                //publicationStatus
                if(d.data.publicationStatus) {
                    $('#lthSolrPublicationFactsTable tbody').append('<tr><td>' + lth_solr_messages.publicationStatus + '</td><td>' + d.data.publicationStatus + '</td></tr>');
                }
                //series???
                //volume
                if(d.data.volume) {
                    $('#lthSolrPublicationFactsTable tbody').append('<tr><td>' + lth_solr_messages.volume + '</td><td>' + d.data.volume + '</td></tr>');
                }
                //issn
                if(d.data.issn) {
                    $('#lthSolrPublicationFactsTable tbody').append('<tr><td>' + lth_solr_messages.issn + '</td><td>' + d.data.issn + '</td></tr>');
                }
                //peerReview
                if(d.data.peerReview) {
                    $('#lthSolrPublicationFactsTable tbody').append('<tr><td>' + lth_solr_messages.peerReview + '</td><td>' + d.data.peerReview + '</td></tr>');
                }
                //bibliographical note
                if(d.data.bibliographicalNote) {
                    $('#lthSolrBibliographicalNote').append('<h3>' + lth_solr_messages.bibliographicalNote + '</h3>' + d.data.bibliographicalNote);
                } else {
                    $('#lthSolrBibliographicalNote').remove();
                }
                //keyword
                if(d.data.keyword) {
                    $('#lthSolrKeywords').prepend('<h3>' + lth_solr_messages.keywords + '</h3>');
                    var keywordsUka=[], keywordsUser=[];
                    for(var i = 0; i < d.data.keyword.length; i++) {
                        if(d.data.keywordType[i].indexOf('UKÄ') > 0) {
                            keywordsUka.push(d.data.keyword[i]);
                        } else {
                            keywordsUser.push(d.data.keyword[i]);
                        }
                    }
                    if(keywordsUka.length > 0) {
                        $('#lthSolrKeywords > .table > tbody').append('<tr><td>' + lth_solr_messages.keywords_uka + '</td><td>' + keywordsUka.join(', ') + '</td></tr>');
                    }
                    if(keywordsUser.length > 0) {
                        $('#lthSolrKeywords > .table > tbody').append('<tr><td>' + lth_solr_messages.keywords_user + '</td><td>' + keywordsUser.join(', ') + '</td></tr>');
                    }
                } else {
                    $('#lthSolrKeywords').remove();
                }
                //event
                if(d.data.eventName) {
                    $('#lthSolrEvent').prepend('<h3>' + d.data.eventType + '</h3>');
                    $('#lthSolrEvent > .table > tbody').append('<tr><td>' + d.data.eventType + '</td><td>' + d.data.eventName + '</td></tr>');
                    if(d.data.eventCountry) {
                        $('#lthSolrEvent > .table > tbody').append('<tr><td>' + lth_solr_messages.country + '</td><td>' + d.data.eventCountry + '</td></tr>');
                    }
                    if(d.data.eventCity) {
                        $('#lthSolrEvent > .table > tbody').append('<tr><td>' + lth_solr_messages.city + '</td><td>' + d.data.eventCity + '</td></tr>');
                    }
                    var eventPeriod = '';
                    if(d.data.startDate && startDate !== '1970-01-01T00:00:00Z') {
                        eventPeriod = d.data.startDate.substr(0,10);
                    }
                    if(d.data.endDate && endDate !== '1970-01-01T00:00:00Z') {
                        eventPeriod +=  ' -- ' + d.data.endDate.substr(0,10);
                    }
                    if(eventPeriod) {
                        $('#lthSolrEvent > .table > tbody').append('<tr><td>' + lth_solr_messages.period + '</td><td>' + eventPeriod + '</td></tr>');
                    }
                } else {
                    $('#lthSolrEvent').remove();
                }
                //cite this
                if(d.data.bibtex) {
                    $('#lthSolrCiteThis').prepend('<h3>' + lth_solr_messages.citeThis + '</h3>');
                    $('#lthSolrCiteThis > ul').append('<li class="nav-item">' +
                        '<a class="nav-link active" id="bibtex-tab" data-toggle="tab" href="#bibtex" role="tab" aria-controls="bibtex" aria-selected="true">BIBTEX</a>' +
                        '</li>');
                    $('#lthSolrCiteThis > .tab-content').append('<div class="tab-pane show active" id="bibtex" role="tabpanel" aria-labelledby="bibtex-tab">' +
                        '<div class="row">' +
                        '<div class="col offset-lg-1">' + d.data.bibtex +
                        '</div></div></div>');
                }
                if(d.data.cite) {
                    var citeArray = d.data.cite.split('<h3>');
                    for(var i = 1; i < citeArray.length; i++) {
                        $('#lthSolrCiteThis > ul').append('<li class="nav-item">' +
                            '<a class="nav-link" id="cite'+i+'-tab" data-toggle="tab" href="#cite'+i+'" role="tab" aria-controls="cite'+i+'" aria-selected="true">' + citeArray[i].split('</h3>').shift() + '</a>' +
                            '</li>');

                        $('#lthSolrCiteThis > .tab-content').append('<div class="tab-pane" id="cite'+i+'" role="tabpanel" aria-labelledby="cite'+i+'-tab">' +
                            '<div class="row">' +
                            '<div class="col offset-lg-1">' + citeArray[i].split('</h3>').pop() +
                            '</div></div></div>');                                
                    }
                }
                //electronic
                if(d.data.electronicIsbn) {
                    
                }
                if(d.data.electronicIsbns) {
                    
                }
                if(d.data.electronicVersionAccessType) {
                    
                }
                if(d.data.electronicVersionDoi || d.data.electronicVersionLink || d.data.electronicVersionFileURL) {
                    
                    var ii=0, iii=0, iv=0, electronicVersion='';
                    for(var i = 0; i < d.data.electronicVersionDoi.length; i++) {
                        if(d.data.electronicVersionDoi[i]) {
                            //if(ii===0) electronicVersion += '<p><b>DOI</b><br />';
                            //electronicVersion += checkOpen(electronicVersionVersionType[i], electronicVersionAccessType[i]);
                            electronicVersion += '<li><i class="fa fa-globe"></i><a style="word-wrap:break-word;" href="'+d.data.electronicVersionDoi[i]+'">'+d.data.electronicVersionDoi[i]+'</a><br /><i>' + d.data.electronicVersionVersionType[i] + '</i></li>';
                            ii++;
                        } else if(d.data.electronicVersionLink[i]) {
                            //if(iii===0) electronicVersion += '<p><b>Länkar</b><br />';
                            //checkOpen(electronicVersionVersionType[i], electronicVersionAccessType[i]);
                            electronicVersion += '<li><i class="fa fa-globe"></i><a style="word-wrap:break-word;" href="'+d.data.electronicVersionLink[i]+'">'+d.data.electronicVersionLink[i]+'</a><br /><i>' + d.data.electronicVersionVersionType[i] + '</i></li>';
                            iii++;
                        } else if(d.data.electronicVersionFileURL[i]) {
                            //if(iv===0) electronicVersion += '<p><b>Dokument</b><br />';
                            //checkOpen(electronicVersionVersionType[i], electronicVersionAccessType[i]);
                            electronicVersion += '<li><i class="fa fa-globe"></i><a style="word-wrap:break-word;" href="'+d.data.electronicVersionFileURL[i]+'">'+d.data.electronicVersionFileURL[i]+'</a><br /><i>' + d.data.electronicVersionVersionType[i] + '</i></li>';
                            iv++;
                        }
                    }

                    $('.col-12.col-lg-4').append('<h2>'+ lth_solr_messages.accessToDocument + '</h2><ul class="list-unstyled">'+electronicVersion + '</ul>');
                }
                if(d.data.electronicVersionFileName) {
                    
                }
                if(d.data.electronicVersionFileURL) {
                    
                }
                if(d.data.electronicVersionLicenseType) {
                    
                }
                if(d.data.electronicVersionLink) {
                    
                }
                if(d.data.electronicVersionMimeType) {
                    
                }
                if(d.data.electronicVersionSize) {
                    
                }
                if(d.data.electronicVersionTitle) {
                    
                }
                if(d.data.electronicVersionVersionType) {
                    
                }
            }
        }
    });
}


function formatBytes(a,b){if(0==a)return"0 Bytes";var c=1024,d=b||2,e=["Bytes","KB","MB","GB","TB","PB","EB","ZB","YB"],f=Math.floor(Math.log(a)/Math.log(c));return parseFloat((a/Math.pow(c,f)).toFixed(d))+" "+e[f]}


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

function addZero(input)
{
    if(input) {
        if(input.length === 1) {
            input = '0' + input;
        }
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
    var syslang = $('html').attr('lang');
    var scope = $('#lth_solr_scope').val();
    var tableLength = $('#lth_solr_no_items').val();
    var path, id, curtailed, endDate, managingOrganisationId, managingOrganisationName, managingOrganisationType;
    var organisationId, organisationName, organisationType, participantId, participantName, participantOrganisationId;
    var participantOrganisationName, participantOrganisationType, participantRole, projectStatus, projectType, startDate, visibility;
    var projectTitle, projectDescription, projectDescriptionType;
    var content, maxClass;
    var inputFacet = '';
    
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
                $('#lthsolr_projects_container div').remove().append('<img class="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
            } else {
                $('.lthsolr_more').html('').addClass('loader');
            }
            $('#lth_solr_facet_container ul li ul li').remove();
        },
        success: function(d) {
            if(d.data) {
                if(d.facet) {
                    
                    //if($('.item-list').length == 0) {
                        if(mobileCheck()) {
                            if($('#lth_solr_facet_container').length == 0) {
                                $('#lthsolr_projects_container').append('<div id="lth_solr_facet_container">'+
                                    '<div style="margin-top:15px;" class="input-group">'+
                                        '<span class="input-group-addon" id="basic-addon1"><i class="fa fa-search fa-sm slsGray20"></i></span>'+
                                        //'<i class="fa fa-search fa-lg slsGray50"></i>' +
                                        '<input type="text" style="font-size:12px;" class="form-control" id="lthsolr_projects_filter" placeholder="" value="" />'+
                                        '</div>'+
                                        '<ul style=""><li><i class="fa fa-angle-right fa-sm slsGray20"></i><a href="javascript:" onclick="$(\'.maxlist-all\').toggle(500);">'+lth_solr_messages.moreFilteringOptions+'</a><ul class="maxlist-all"><li></li></ul></li></ul></div>');
                                $('#lthsolr_projects_filter').keyup(function() {
                                    listProjects(0, getFacets(), $(this).val().trim(), $("#lthsolr_sort").val(), 0,'', action);
                                });
                            }
                        } else {
                            if($('#lth_solr_facet_container').length == 0) {
                                $('#content_navigation,#subnavigation').append('<div id="lth_solr_facet_container">'+
                                        '<b>' + lth_solr_messages.filterSearchResult+'</b>'+
                                        '<div style="margin-top:15px;" class="input-group">'+
                                        '<span class="input-group-addon" id="basic-addon1"><i class="fa fa-search fa-sm slsGray20"></i></span>'+
                                        //'<i class="fa fa-search fa-lg slsGray50"></i>' +
                                        '<input type="text" style="font-size:12px;height:31px;" class="form-control" id="lthsolr_projects_filter" placeholder="" value="" />'+
                                        '</div>'+
                                        '<ul style="border-top:1px #dedede solid;margin-top:15px;padding-top:7px;"><li><ul><li></li></ul></li></ul></div>');
                                $('#lthsolr_projects_filter').keyup(function() {
                                    listProjects(0, getFacets(), $(this).val().trim(), $("#lthsolr_sort").val(), 0,'', action);
                                });
                            }
                        }
                        var facet, count, facetHeader;
                        $.each( d.facet, function( key, value ) {
                            maxClass='';
                            more='';
                            content = '';
                            i=0;
                            $.each( value, function( key1, value1 ) {
                                if(i > 4) {
                                    maxClass = ' class="maxlist-hidden"';
                                    more = '<li class="maxlist-more"><i class="fa fa-chevron-right"></i><a href="#">' + lth_solr_messages.more + '</a></li>';
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
                                    content += '<li' + maxClass + ' style=""><label>';
                                    content += facet.capitalize().replace(/_/g, ' ') + '&nbsp;[' + count + '] ';
                                    content += '<input type="checkbox" class="lth_solr_facet item-list" name="lth_solr_facet" value="' + key + '###' + facet + '"' + facetCheck + '>';
                                    content += '</label></li>';
                                }
                                i++;
                            });

                            
                            /*$('.lthsolr_facet_close').click(function() {
                                $('#lth_solr_facet_container').toggle(500);
                                //$("#lthsolr_projects_container").toggleClass('expand', 500);
                            });*/
                            $('#lth_solr_facet_container ul > li > ul').append('<li style=""><b>'+facetHeader+'</b></li>' + content + more + '');
                            i=0;
                        });
                        
                        //createFacetClick('listPublications', sorting, action);
                    //}
                }
                
                var projectDetailPage = 'visa';
                if(syslang=='en') {
                    projectDetailPage = 'show';
                }
                
                $.each( d.data, function( key, aData ) {
                    path = '';
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
                    
                    id = aData.id;
                    
                    curtailed = aData.curtailed;
                    endDate = aData.endDate;
                    managingOrganisationId = aData.managingOrganisationId;
                    managingOrganisationName = aData.managingOrganisationName;
                    managingOrganisationType = aData.managingOrganisationType;
                    organisationId = aData.organisationId;
                    organisationName = aData.organisationName;
                    organisationType = aData.organisationType;
                    participantId = aData.participantId;
                    participantName = aData.participantName;
                    participantOrganisationId = aData.participantOrganisationId;
                    participantOrganisationName = aData.participantOrganisationName;
                    participantOrganisationType = aData.participantOrganisationType;
                    participantRole = aData.participantRole;
                    projectDescription = aData.projectDescription;
                    projectDescriptionType = aData.projectDescriptionType;
                    projectStatus = aData.projectStatus;
                    projectTitle = aData.projectTitle;
                    projectType = aData.projectType;
                    startDate = aData.startDate;
                    visibility = aData.visibility;

                    if(projectTitle) projectTitle = '<a href="' + path + '/' + projectTitle.replace(/[^\w\s-]/g,'').replace(/ /g,'-').toLowerCase() + '(' + id + ')">' + projectTitle + '</a>';
                    
                    var template = $('#solrProjectTemplate').html();

                    template = template.replace('###id###', id);
                    template = template.replace('###title###', projectTitle);
                    template = template.replace('###participants###', participantName);
                    template = template.replace('###projectStartDate###', startDate.substr(0,10));
                    template = template.replace('###projectEndDate###', endDate.substr(0,10));
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
                    $('#lthsolr_projects_container').parent().height($('#lthsolr_projects_container').height());
                    $('#lth_solr_facet_container').height($('#lthsolr_projects_container').height());
                    $('#lthsolr_projects_container, #lth_solr_facet_container').css('float','left');
                }*/
            }
        }
    });
}


function showProject()
{
    var id, projectTitle, curtailed, endDate, managingOrganisationId, managingOrganisationName, managingOrganisationType;
    var organisationId, organisationName, organisationType, participantId, participantName, participantOrganisationId;
    var participantOrganisationName, participantOrganisationType, participantRole, projectStatus, projectType, startDate, visibility;
    var projectTitle, projectDescriptionType, homepage;
    var participants = '';
    var projectDescription = '';
    
    var syslang = $('html').attr('lang');
    var publicationlink = $('#lth_solr_publicationlink').val();
    var peopleLink = $('#lth_solr_peoplelink').val();
    
    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID : 'lth_solr',
            action : 'showProject',
            scope : $('#lth_solr_scope').val(),
            sys_language_uid : $('#sys_language_uid').val(),
            sid : Math.random(),
        },
        //contentType: "application/json; charset=utf-8",
        dataType: 'json',
        beforeSend: function () {
            $('#lthsolr_projects_container div').remove().append('<img class="lthsolr_loader" style="height:16px; width:16px;" src="/fileadmin/templates/images/ajax-loader.gif" />');
        },
        success: function(d) {
            //console.log(d);
            var staffDetailPage = 'visa';
            if(syslang=='en') {
                staffDetailPage = 'show';
            }
            if(d.data) {
                id = d.data.id;
                curtailed = d.data.curtailed;
                endDate = d.data.endDate.substr(0,10);
                managingOrganisationName = d.data.managingOrganisationName;
                organisationId = d.data.organisationId;
                organisationName = d.data.organisationName;
                organisationType = d.data.organisationType;
                
                if(d.data.participantName) {
                    participantId = d.data.participantId;
                    participantName = d.data.participantName;
                    participantOrganisationId = d.data.participantOrganisationId;
                    participantOrganisationName = d.data.participantOrganisationName;
                    participantOrganisationType = d.data.participantOrganisationType;
                    participantRole = d.data.participantRole;
                    var participantIdArray = participantId.split(',');
                    var participantNameArray = participantName.split(',');
                    
                    for (var j = 0; j < participantNameArray.length; j++) {
                        if(participantIdArray[j]) {
                            if(peopleLink) {
                                homepage = peopleLink + '/' + staffDetailPage + '/' + 
                                        participantNameArray[j].trim().replace(' ','-') + '('+participantIdArray[j].trim()+')';
                            } else {
                                homepage = window.location.href.split(staffDetailPage).shift() + staffDetailPage + '/' + 
                                        participantNameArray[j].trim().replace(' ','-') + '('+participantIdArray[j].trim()+')';
                            }
                        }
                        participants += '<li><a href="' + homepage + '">' + participantNameArray[j].trim() + '</a></li>'
                    }
                    
                    participants = '<div class="card"><div class="card-header" id="headingParticipants"><h5 class="mb-0">'+
                    '<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseParticipants" aria-expanded="true" aria-controls="collapseParticipants">'+
                        'Participants'+
                    '</button></h5></div>'+
                    '<div id="collapseParticipants" class="panel-collapse collapse show in" aria-labelledby="headingParticipants" data-parent="#lthSolrAccordion">'+
                    '<div class="card-body"><ul class="list">' + participants + '</ul></div></div></div>';
                }
                
                if(d.data.projectDescription) {
                    $.each(d.data.projectDescription, function( descKey, descData ) {
                        if(descData && descData != 'false') {
                            projectDescription += '<div class="card"><div class="card-header" id="headingDescription"><h5 class="mb-0">'+
                            '<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseDescription" aria-expanded="true" aria-controls="collapseDescription">'+
                                'Description'+
                            '</button></h5></div>'+
                            '<div id="collapseDescription" class="collapse show in" aria-labelledby="headingDescription" data-parent="#lthSolrAccordion">'+
                            '<div class="card-body">'+ descData + '</div></div></div>';
                            return false;
                        }
                    });
                    //projectDescription = d.data.projectDescription;
                    
                }
                
                //projectDescriptionType = d.data.projectDescriptionType;
                projectStatus = d.data.projectStatus;
                projectTitle = d.data.projectTitle;
                startDate = d.data.startDate.substr(0,10);
                
                var template = $('#solrProjectTemplate').html();
               
                template = template.replace('###endDate###', endDate.substr(0,12));
                //template = template.replace('###managingOrganisationName###', managingOrganisationName);
                template = template.replace('###organisationId###', organisationId);
                template = template.replace('###participants###', participants);
                template = template.replace('###projectDescription###', projectDescription);
                template = template.replace('###projectDescriptionType###', projectDescriptionType);
                template = template.replace('###projectStatus###', projectStatus);
                template = template.replace('###startDate###', startDate.substr(0,12));

                $('#lthsolr_projects_container').html(template);
                
                if(!projectDescription) {
                    $('.more-content').parent().remove();
                }
                    
                $('#page_title h1, article h1').text(projectTitle);
            }
        }
    });
}


function toggleFacets()
{
    $('.maxlist-more a').on( 'click', function () {
        //console.log($(this).parent().prev());
        $(this).parent().parent().find('.maxlist-hidden').toggle('slow');
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
    } else {
        return '';
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
            var solrId = $('#lthsolr_show_staff_container').parent().attr('id');
            $('#'+solrId).parent().find('> div:not(#'+solrId+')').remove();
            $('#lthsolr_show_staff_container').append('<img class="lthsolr_loader" id="lthsolr_loader_staff" src="/fileadmin/templates/images/ajax-loader.gif" />');
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
                    var title = '', phone = '', roomNumber = '';

                    //template = template.replace("###displayName###", displayName);
                    
                    template = template.replace(/###email###/g, aData.email);

                    var affiliation='';
                    
                    for (var i=0; i<aData.organisationId.length; i++) {
                        if(affiliation) affiliation += '<br />';
                        
                        /*if(scope===aData.organisationId[i]) {
                            curI=i;
                        }*/
                        /*if(aData.title) {
                            if(aData.title[i]) affiliation += '<b>'+titleCase(aData.title[i])+'</b>';
                        }*/
                        if(aData.organisationName) {
                            if(aData.organisationName[i]) affiliation += aData.organisationName[i];
                        }
                        roomNumber = '';
                        if(aData.roomNumber) {
                            roomNumber = aData.roomNumber[i];
                            if(roomNumber) {
                                roomNumber = ' (' + lth_solr_messages.room + ' ' + roomNumber + ')';
                            } else {
                                roomNumber = '';
                            }
                            if(roomNumber) affiliation += roomNumber;
                        }
                        phone = '';
                        if(aData.phone) {
                            if(aData.phone[i] && aData.phone[i] !== 'NULL') {
                                phone = addBreak(aData.phone[i]);
                                phone = phone.replace('+4646222', '+46 46 222 ').replace(/(.{2}$)/, ' $1');
                            }
                        }
                        if(aData.mobile) {
                            if(aData.mobile[i] && aData.mobile[i] !== 'NULL') {
                                if(phone) phone += ', ';
                                phone += addBreak('+46 ' + aData.mobile[i].replace(/ /g, '').replace('+46','').replace(/(\d{2})(\d{3})(\d{2})(\d{2})/, "$1 $2 $3 $4"));
                            }
                        }
                        if(phone) affiliation += phone;
                        
                        if(aData.organisationStreet) {
                            if(aData.organisationStreet[i]) affiliation += aData.organisationStreet[i];
                        }
                        if(aData.organisationPostalAddress) {
                            if(aData.organisationPostalAddress[i]) affiliation += addBreak(aData.organisationPostalAddress[i].toString().split('$').join(', '));
                        }

                        //template = template.replace('###visitingAddress###', ostreet + ' ' + ocity + addBreak(ophone));
                        //template = template.replace('###postalAddress###', addBreak(organisationPostalAddress));
                    }

                    
                    template = template.replace('###affiliation###', affiliation);
                    //if(aData.title) title = aData.title.join(', ');
                    if(aData.title) title = Array.from(new Set(aData.title)).join(', ');
                    /*
                    if(aData.organisationName) organisationName = aData.organisationName[0];
                    if(aData.phone) {
                        phone = aData.phone[0];
                    }
                    if(phone) phone = phone.replace('+4646222', '+46 46 222 ').replace(/(.{2}$)/, ' $1');
                    if(aData.mobile) {
                        if(phone) phone += ', ';
                        phone += aData.mobile[0];
                    }*/
                    
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
                    $('#page_title h1, article h1').text(displayName).append('<h2>'+title+'</h2>');
                    
                    //template = template.replace('###title###', titleCase(title));
                    //template = template.replace('###phone###', addBreak(phone));

                    //template = template.replace('###organisationName###', organisationName);

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


function createFacetClick(listType, sorting, action)
{
    $('.lth_solr_facet').click(function() {
        if(listType==='listStaff') {
            listStaff(0, getFacets(), $('#lthsolr_staff_filter').val().trim(),false,false);
        } else if(listType==='listPublications') {
            listPublications(0, getFacets(), $('#lthsolr_publications_filter').val().trim(),sorting,false,'',action);
        } else if(listType==='listStudentPapers') {
            listStudentPapers(0, getFacets(), $('#lthsolr_publications_filter').val().trim(),false);
        } else if(listType==='listOrganisationStaff') {
            listOrganisationStaff(getFacets(), $('#lthsolr_organisation_filter').val().trim());
        }
    });
}


function getSpinner(sysLang)
{
    var loadText;
    if(sysLang==='sv') {
        loadText = 'Laddar...';
    } else {
        loadText = 'Loading...';
    }
    
    var content = '<div class="spinner text-center">';
    content += '<p class="text-primary"><i class="fal fa-circle-notch fa-3x fa-spin"></i></p>';
    content += '<p class="font-weight-bold">' + loadText + '</p>';
    content += '</div>';
    
    return content;
}


function formatPhone(phone)
{
    var phoneTmp = phone;
    //console.log(phone.substr(8,2)+phone.substr(-2));
    if(phoneTmp.indexOf('4646222')>0) phoneTmp = '+46 46 222 ' + phone.substr(8,2) + ' ' + phone.substr(-2);
    return phoneTmp;
}


function getActiveCheckboxes()
{
    //$('#facetSub' + i).append('<li id="' + key + facetVal + '" class="nav-item lth_solr_facet"><a class="nav-link nav-link-sm" data-val="' + key + '###' + facetVal + '" href="javascript:">' + facetVal.replace(/_/g, ' ') + ' (' + count + ')</a></li>');

    var facet = [];
    //console.log($(".lth_solr_facet.active").length);
    $(".lth_solr_facet.active").each(function() {
        facet.push($(this).val());
    });
    
    if(facet.length > 0) {
        return JSON.stringify(facet);
    } else {
        return null;
    }
}


function getActiveFacets(activeClass='')
{
    //$('#facetSub' + i).append('<li id="' + key + facetVal + '" class="nav-item lth_solr_facet"><a class="nav-link nav-link-sm" data-val="' + key + '###' + facetVal + '" href="javascript:">' + facetVal.replace(/_/g, ' ') + ' (' + count + ')</a></li>');

    var facet = [];
    //console.log($(".lth_solr_facet.active").length);
    $(activeClass + " .lth_solr_facet.active").each(function() {
        facet.push($(this).attr('data-val'));
    });
    
    if(facet.length > 0) {
        return JSON.stringify(facet);
    } else {
        return null;
    }
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

function checkOpen(electronicVersionVersionType, electronicVersionAccessType)
{
    if((electronicVersionVersionType.toLowerCase() === 'publicerad version' || electronicVersionVersionType.toLowerCase() === 'final published version') &&
        (electronicVersionAccessType.toLowerCase() === 'öppen' || electronicVersionAccessType.toLowerCase() === 'open')) {
        return '<i class="fa fa-unlock"></i>';
    } else {
        return '';
    }
}


function isEven(n)
{
   return n % 2 == 0;
}