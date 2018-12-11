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
                        link = 'visa/'+encodeURIComponent(courseTitle+'('+courseCode+')');
                    } else {
                        link = 'show/'+encodeURIComponent(courseTitle+'('+courseCode+')');
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
    var round = $('#lth_solr_round').val();
    var abstract,department,courseCode,courseTitle,credit,homepage,ratingScale;

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
                round: round,
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
            if(d.data.abstract) {
                abstract = '';
                department = '';
                courseCode = '';
                courseTitle = '';
                credit = '';
                homepage = '';
                ratingScale = '';
                if(d.data.abstract) abstract = d.data.abstract;
                if(d.data.department) department = d.data.department;
                if(d.data.courseCode) courseCode = d.data.courseCode;
                if(d.data.courseTitle) courseTitle = d.data.courseTitle;
                if(d.data.credit) credit = d.data.credit;
                if(d.data.homepage) homepage = d.data.homepage;
                if(d.data.ratingScale) ratingScale = d.data.ratingScale;
                
                //console.log(d.data.abstract);
                $('h1').text(jobTitle).attr('style', 'margin-bottom:18px !important;max-width:650px;');
                $('.lthsolr_job_apply_button').attr('href',loginAndApplyURI).text(lth_solr_messages.applyButtonText).show();
                $('.breadcrumb li:last').removeClass('active').wrapInner('<a href="/'+lth_solr_messages.job+'/"></a>');
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


function listStatistics()
{
    var program = $('#lth_solr_program').val();
    var round = $('#lth_solr_round').val();
    var syslang = $('html').attr('lang');
    var id,statTermin,statType,statTitle,statCode,statVal1,statVal2,statApplicants,newRow,first;
    var antagna = 0;

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
                        newRow += '</p><table class="table lth_solr_stat_program_table"><thead class=""><tr></tr></thead><tbody><tr></tr></tbody></table>';
                        newRow += '</div>';
                        $('#lthsolr_statistics_container').append(newRow);
                        
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
                                $('.lth_solr_stat_program_table thead tr').append('<th>'+tmpArray[0]+'</th>');
                                $('.lth_solr_stat_program_table tbody tr').append('<td>'+lthSolrRound(tmpArray[1])+'</td>');
                                antagna = antagna + parseInt(tmpArray[2]);
                            }
                            
                        }
                        $('.lth_solr_stat_program_table thead tr').prepend('<th>Antagna</th>');
                        $('.lth_solr_stat_program_table tbody tr').prepend('<td>'+antagna.toString()+'</td>');
                        $('#lthsolr_statistics_container').append('<p><i class="fas fa-info-circle" onclick="lthSolrExplainStat();"></i></p>');
                        $('#lthsolr_statistics_container').append('<p><a href="../antagning-och-behoerighet/">Läs mer om antagning</a></p>');
                        /*$('#lthsolr_statistics_container .fa-info-circle').click(function(){
                            alert('BI=Gymnasiebetyg\nBII=Gymnasiebetyg med komplettering\nHP=Högskoleprov\nBF=Folkhögskola');
                        });*/
                    } else {
                        $('#lthsolr_statistics_container tbody').append('<tr></tr>');
                        $('#lthsolr_statistics_container tbody tr:last').append('<td>' + statTitle+'</td>');
                        $('#lthsolr_statistics_container tbody tr:last').append('<td>' + statApplicants.split(',')[0] + '</td>');
                        $('#lthsolr_statistics_container tbody tr:last').append('<td>' + statApplicants.split(',')[1] + '</td>');

                        var ii = 0;
                        for (var i = 0; i < statVal2.length; i++) {
                            var tmpArray = statVal2[i].split(',');
                            /*for (var ii = 0; ii < tmpArray.length; ii++) {
                                if(ii!==0) {
                                    newRow += '<td>' + tmpArray[ii] + '</td>';
                                }
                            }*/
                            if(tmpArray[0] === 'BI' || tmpArray[0] === 'BII' || tmpArray[0] === 'HP' || tmpArray[0] === 'BF') {
                                //$('#lthsolr_statistics_container thead tr').append('<th>'+tmpArray[0]+'</th>');
                                $('#lthsolr_statistics_container tbody tr:last').append('<td>'+lthSolrRound(tmpArray[1])+'</td>');
                                antagna = antagna + parseInt(tmpArray[2]);
                                ii++;
                            }
                            
                        }
                        var rest = 4 - ii;
                        
                        for (var ii = 0; ii < rest; ii++) {
                            $('#lthsolr_statistics_container tbody tr:last').append('<td></td>');
                        }
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


function lthSolrRound(input)
{
    if(parseFloat(input)) {
        input = Math.round(input * 100) / 100;
    }
    return input;
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
            
            
            //$('.article').html('<div class="lthPackageLoader"></div>');
            //$('.lthsolr_loader').remove();
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
    var courseCode,courseTitle,courseYear,credit,homepage,id,optional,programCode,programDirection,programTitle,ratingScale;
    var i = 0;

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
                    //console.log(Object.keys(d.data).length);
                    $.each( programData, function (arskursKey, arskursData) {
                        if(arskursData) {
                            if(Object.keys(d.data).length > 1) {
                                $("[id='"+programKey+"']").append('<div id="'+programKey+arskursKey+'" class="card-header">Årskurs'+arskursKey+'</div>');
                            } else {
                                $("[id='"+programKey+"'] > thead > tr").append('<th>Årskurs'+arskursKey+'</th>');
                                $("[id='"+programKey+"'] > tbody > tr").append('<td><div id="'+programKey+arskursKey+'"></div></td>');
                            }
                        }
                        $.each( arskursData, function (inriktningKey, inriktningData) {
                            if(inriktningData) {
                                $("[id='"+programKey+arskursKey+"']").append('<div id="'+programKey+arskursKey+inriktningKey+'">'+inriktningKey+'</div>');
                            }
                            $.each( inriktningData, function (optionalKey, optionalData) {
                                if(optionalKey === 'obligatorisk') {
                                    
                                }
                                if(optionalData) {
                                    if(optionalKey === 'obligatorisk') {
                                        $("[id='"+programKey+arskursKey+inriktningKey+"']").append('<div>' + titleCase(optionalKey.replace('_','')) + '</div><ul id="'+programKey+arskursKey+inriktningKey+optionalKey+'" class="list-group list-group-flush"></ul>');
                                    } else {
                                        $("[id='"+programKey+arskursKey+inriktningKey+"']").append('<div class="accordion" id="accordion'+i+'"><div class="card">'+
        '<div class="card-header" id="heading'+i.toString()+'"><h5><button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapse'+i.toString()+
        '" aria-expanded="true" aria-controls="collapse'+i.toString()+'">'+
            '<div class="float-right"><span class="collapse-hide"><i class="fal fa-chevron-up"></i></span><span class="collapse-show"><i class="fal fa-chevron-down"></i></span></div>'+
            titleCase(optionalKey.replace('_','')) +
          '</button></h5></div><div id="collapse'+i.toString()+'" class="collapse" aria-labelledby="heading'+i.toString()+'" data-parent="#accordion'+i.toString()+'"><div class="card-body">'+
            '<ul id="'+programKey+arskursKey+inriktningKey+optionalKey+'" class="list-group list-group-flush"></ul></div></div></div></div>');
                                        i++;
                                    }
                                }
                                     //collapse_6
                                     //collapse_6
                                $.each( optionalData, function (courseKey, courseData) {
                                    //console.log(optionalData);
                                    courseCode = '';
                                    courseTitle = '';
                                    courseYear = '';
                                    credit = '';
                                    homepage = '';
                                    id = '';
                                    optional = '';
                                    programCode = '';
                                    programDirection = '';
                                    programTitle = '';
                                    ratingScale = '';

                                    if(courseData.courseCode) courseCode = courseData.courseCode.toUpperCase();
                                    if(courseData.courseTitle) courseTitle = courseData.courseTitle;
                                    if(courseData.courseYear) courseYear = courseData.courseYear;
                                    if(courseData.credit) credit = courseData.credit;
                                    if(courseData.id) id = courseData.id;
                                    if(courseData.optional) optional = courseData.optional;
                                    if(courseData.programDirection) programDirection = courseData.programDirection;
                                    if(courseData.ratingScale) ratingScale = courseData.ratingScale;

                                    /*template = template.replace('###id###', id);
                                    template = template.replace('###courseTitle###', courseTitle);
                                    template = template.replace('###courseYear###', courseYear);
                                    template = template.replace('###credit###', credit);
                                    template = template.replace('###optional###', optional);
                                    template = template.replace('###programDirection###',programDirection);
                                    template = template.replace('###ratingScale###',ratingScale);*/

                                    $("[id='"+programKey+arskursKey+inriktningKey+optionalKey+"']").append('<li id="'+id+'" class="list-group-item">'+courseCode+' '+courseTitle + '</li>');
                                    $("[id='"+id+"']").click(function(){
                                        $('#compareModal').modal('toggle');
                                        showCompare(id);
                                    });
                                });
                            });
                            //var template = $('#solrCompareTemplate').html();
                        });
                    }); 
                });
            }
        }
    });
}


function showCompare(scope)
{
    var syslang = $('html').attr('lang');
    var abstract,courseCode,courseTitle,courseYear,credit,homepage,id,optional,programCode,programDirection,programTitle,ratingScale;

    $.ajax({
        type : 'POST',
        url : 'index.php',
        data: {
            eID: 'lth_solr',
            action: 'showCompare',
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
                    id = '';
                    optional = '';
                    programCode = '';
                    programDirection = '';
                    programTitle = '';
                    ratingScale = '';
                    if(aData.abstract) abstract = aData.abstract;
                    if(aData.courseCode) courseCode = aData.courseCode.toUpperCase();
                    if(aData.courseTitle) courseTitle = aData.courseTitle;
                    $('#compareModal .modal-title').html(courseCode + ' ' + courseTitle);
                    $('#compareModal .modal-body').html(abstract);
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
    var abstract='',additionalLink = '',authorId='',authorExternal='',authorName='',authorOrganisation,authorReverseName,authorReverseNameShort,bibtex,cite,doi,edition,electronicIsbns;
    var electronicVersionAccessType, electronicVersionDoi, electronicVersionFileName, electronicVersionFileURL, electronicVersionLicenseType;
    var electronicVersion, electronicVersionLink, electronicVersionMimeType, electronicVersionSize, electronicVersionTitle, electronicVersionVersionType, endDate;
    var externalOrganisations,event,eventName,eventLink,eventType,eventCity,eventCountry,hostPublicationTitle,id,issn,journalNumber,journalTitle,keywords_uka,keywords_user;
    var language,pages,numberOfPages,openAccessPermission;
    var organisationName,organisationSourceId,organisationId,peerReview,placeOfPublication;
    var printIsbns,publicationStatus,publicationDateYear,publicationDateMonth,publicationDateDay,publicationType,publicationTypeUri,publisher,startDate,supervisors;
    var title,volume;
    
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
                authorOrganisation = d.data.authorOrganisation;
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
                
                //electronic version
                if(electronicVersionVersionType) {
                    for(var i = 0; i < electronicVersionVersionType.length; i++) {
                        if(electronicVersionDoi[i]) {
                            electronicVersion = '<p><b>DOI</b><br />';
                            electronicVersion += checkOpen(electronicVersionVersionType[i], electronicVersionAccessType[i]);
                            electronicVersion += '<a href="'+electronicVersionDoi[i]+'">'+electronicVersionDoi[i]+'</a> (<i>' + electronicVersionVersionType[i] + '</i>)</p>';
                        } else if(electronicVersionFileURL[i]) {
                            electronicVersion = '<p><b>Dokument</b><br />';
                            checkOpen(electronicVersionVersionType[i], electronicVersionAccessType[i]);
                            electronicVersion += '<a href="'+electronicVersionFileURL[i]+'">'+electronicVersionFileName+'</a> (<i>' + electronicVersionVersionType[i] + addComma(formatBytes(electronicVersionSize[i],0)) + addComma(electronicVersionMimeType[i]) + '</i>)</p>';                           
                        } else if(electronicVersionLink[i]) {
                            electronicVersion = '<p><b>Länkar</b><br />';
                            checkOpen(electronicVersionVersionType[i], electronicVersionAccessType[i]);
                            electronicVersion += '<a href="'+electronicVersionLink[i]+'">'+electronicVersionLink[i]+'</a> (<i>' + electronicVersionVersionType[i] + '</i>)</p>';
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
    var participantOrganisationName, participantOrganisationType, participantRole, participants, projectStatus, projectType, startDate, visibility;
    var projectTitle, projectDescription, projectDescriptionType, homepage;
    
    var syslang = $('html').attr('lang');
    
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
                endDate = d.data.endDate;
                managingOrganisationName = d.data.managingOrganisationName;
                organisationId = d.data.organisationId;
                organisationName = d.data.organisationName;
                organisationType = d.data.organisationType;
                
                if(d.data.participantId) {
                    participantId = d.data.participantId;
                    participantName = d.data.participantName;
                    participantOrganisationId = d.data.participantOrganisationId;
                    participantOrganisationName = d.data.participantOrganisationName;
                    participantOrganisationType = d.data.participantOrganisationType;
                    participantRole = d.data.participantRole;
                    var participantIdArray = participantId.split(',');
                    var participantNameArray = participantName.split(',');
                    
                    for (var j = 0; j < participantIdArray.length; j++) {
                        homepage = window.location.href.split(staffDetailPage).shift() + staffDetailPage + '/' + 
                                participantNameArray[j].trim().replace(' ','-') + '('+participantIdArray[j].trim()+')(participant)';
                        participants += '<li><a href="' + homepage + '">' + participantNameArray[j].trim() + '</a></li>'
                    }
                    
                    participants = '<div class="card"><div class="card-header" id="headingParticipants"><h5 class="mb-0">'+
                    '<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseParticipants" aria-expanded="true" aria-controls="collapseParticipants">'+
                        'Participants'+
                    '</button></h5></div>'+
                    '<div id="collapseParticipants" class="collapse" aria-labelledby="headingParticipants" data-parent="#lthSolrAccordion">'+
                    '<div class="card-body"><ul class="list">' + participants + '</ul></div></div></div>';
                }
                
                if(d.data.projectDescription) {
                    projectDescription = d.data.projectDescription;
                    projectDescription = '<div class="card"><div class="card-header" id="headingDescription"><h5 class="mb-0">'+
                    '<button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseDescription" aria-expanded="true" aria-controls="collapseDescription">'+
                        'Description'+
                    '</button></h5></div>'+
                    '<div id="collapseDescription" class="collapse show" aria-labelledby="headingDescription" data-parent="#lthSolrAccordion">'+
                    '<div class="card-body">'+ projectDescription + '</div></div></div>';
                }
                
                projectDescriptionType = d.data.projectDescriptionType;
                projectStatus = d.data.projectStatus;
                projectTitle = d.data.projectTitle;
                projectType = d.data.projectType;
                startDate = d.data.startDate;
                visibility = d.data.visibility;
                
                var template = $('#solrProjectTemplate').html();
               
                template = template.replace('###endDate###', endDate.substr(0,12));
                template = template.replace('###managingOrganisationName###', managingOrganisationName);
                template = template.replace('###organisationId###', organisationId);
                template = template.replace('###organisationName###', organisationName);
                template = template.replace('###organisationType###', organisationType);
                template = template.replace('###participants###', participants);
                template = template.replace('###projectDescription###', projectDescription);
                template = template.replace('###projectDescriptionType###', projectDescriptionType);
                template = template.replace('###projectStatus###', projectStatus);
                template = template.replace('###projectType###', projectType);
                template = template.replace('###startDate###', startDate.substr(0,12));
                template = template.replace('###visibility###', d.data.visibility);

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