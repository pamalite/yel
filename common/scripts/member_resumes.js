var selected_tab = 'li_cover_note';
var order_by = 'modified_on';
var order = 'desc';
var work_experiences_count = -1;
var educations_count = 0;
var technical_skills_count = 0;

var work_experiences = new Array();
var educations = new Array();
var technical_skills = new Array();
var skill_sets = null;
var is_new_resume = false;

function resume_is_new() {
    is_new_resume = true;
}

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function save_cover_note(_show_experience) {
    if ($('name').value == "" || $('name').value == null) {
        $('name').value = 'Untitled Resume';
    }
    
    var params = 'id=' + $('resume_id').value;
    params = params + '&action=save_cover_note&member=' + id;
    params = params + '&name=' + $('name').value;
    params = params + '&cover_note=' + $('cover_note').value;
    
    var uri = root + "/members/resume_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while saving resume.');
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            $('resume_id').value = ids[0].childNodes[0].nodeValue;
            $('div_resume_title').set('html', $('name').value);
            is_new_resume = false;
            if (_show_experience) {
                show_experiences();
            }
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Saving resume...');
        }
    });
    
    request.send(params);
}

function show_resume(resume_id) {
    $('div_resume_form').setStyle('display', 'block');
    $('div_resumes').setStyle('display', 'none');
    $('div_upload_resume_form').setStyle('display', 'none');
    $('resume_id').value = resume_id;
    show_cover_note();
}

function add_new_resume() {
    $('div_resume_form').setStyle('display', 'block');
    $('div_upload_resume_form').setStyle('display', 'none');
    $('div_resumes').setStyle('display', 'none');
    $('resume_id').value = "0";
    $('div_resume_title').set('html', 'Untitled Resume');
    $('name').value = '';
    //$('private').checked = false;
    $('cover_note').value = '';
    resume_is_new();
    show_cover_note();
}

function upload_new_resume() {
    $('resume_id').value = '0';
    if (arguments.length > 0) {
        if (typeof arguments[0] == 'string') {
            $('resume_id').value = arguments[0];
        }
    }
    $('div_resumes').setStyle('display', 'none');
    $('div_upload_resume_form').setStyle('display', 'block');
    $('div_resume_form').setStyle('display', 'none');
}

function show_resumes() {
    if (is_new_resume) {
        var discard = confirm('Are you sure to discard the current new resume?');
        if (!discard) {
            return false;
        }
        
        is_new_resume = false;
    }
    
    $('div_resumes').setStyle('display', 'block');
    $('div_upload_resume_form').setStyle('display', 'none');
    $('div_resume_form').setStyle('display', 'none');
    
    var params = 'id=' + id;
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/members/resumes_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading resumes.');
                return false;
            }
            
            var ids = xml.getElementsByTagName('id');
            var names = xml.getElementsByTagName('name');
            var modified_ons = xml.getElementsByTagName('modified_date');
            var file_hashes = xml.getElementsByTagName('file_hash');
            
            var html = '<table id="list" class="list">';
            if (ids.length <= 0) {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">Please click on the \"Create New Resume\" or the \"Upload Resume\" button to get started.</div>';

            } else {
                for (var i=0; i < ids.length; i++) {
                    var resume_id = ids[i];
                    
                    html = html + '<tr id="'+ resume_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";

                    if (file_hashes[i].childNodes.length > 0) {
                        html = html + '<td class="title"><input type="button" value="Replace Resume" onClick="upload_new_resume(\'' + resume_id.childNodes[0].nodeValue + '\');" />&nbsp;<a href="resume.php?id=' + resume_id.childNodes[0].nodeValue + '&member=' + id + '">' + names[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    } else {
                        html = html + '<td class="title"><a href="#" onClick="show_resume(\'' + resume_id.childNodes[0].nodeValue + '\')">' + names[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    }
                    
                    html = html + '<td class="date">' + modified_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            set_status('Loading resumes...');
        }
    });
    
    request.send(params);
}

function start_upload() {
    $('upload_progress').setStyle('display', 'block');
    set_status('Uploading resume...')
    return true;
}

function stop_upload(success) {
    var result = '';
    $('upload_progress').setStyle('display', 'none');
    if (success == 1) {
        show_resumes();
        return true;
    } else {
        set_status('An error occured while uploading your resume. Make sure your resume file meets the conditions stated below.');
        return false;
    }
}

function set_mouse_events() {
    $('li_resumes').addEvent('mouseover', function() {
        $('li_resumes').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_resumes').addEvent('mouseout', function() {
        $('li_resumes').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_cover_note').addEvent('mouseover', function() {
        $('li_cover_note').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_cover_note').addEvent('mouseout', function() {
        $('li_cover_note').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_experiences').addEvent('mouseover', function() {
        $('li_experiences').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_experiences').addEvent('mouseout', function() {
        $('li_experiences').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_educations').addEvent('mouseover', function() {
        $('li_educations').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_educations').addEvent('mouseout', function() {
        $('li_educations').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_skills').addEvent('mouseover', function() {
        $('li_skills').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_skills').addEvent('mouseout', function() {
        $('li_skills').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
    
    $('li_technical_skills').addEvent('mouseover', function() {
        $('li_technical_skills').setStyles({
            'color': '#FF0000',
            'text-decoration': 'underline'
        });
    });
    
    $('li_technical_skills').addEvent('mouseout', function() {
        $('li_technical_skills').setStyles({
            'color': '#000000',
            'text-decoration': 'none'
        });
    });
}

function show_cover_note() {
    selected_tab = 'li_cover_note';
    $('li_cover_note').setStyle('border', '1px solid #CCCCCC');
    $('li_experiences').setStyle('border', '1px solid #0000FF');
    $('li_educations').setStyle('border', '1px solid #0000FF');
    $('li_skills').setStyle('border', '1px solid #0000FF');
    $('li_technical_skills').setStyle('border', '1px solid #0000FF');
    
    $('div_cover_note').setStyle('display', 'block');
    $('div_experiences').setStyle('display', 'none');
    $('div_educations').setStyle('display', 'none');
    $('div_skills').setStyle('display', 'none');
    $('div_technical_skills').setStyle('display', 'none');
    
    var resume_id = $('resume_id').value;
    var resume_title = 'Untitled Resume';
    if (resume_id > 0) {
        var params = 'id=' + resume_id + '&member=' + id + '&action=get_cover_note';
        var uri = root + "/members/resume_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    set_status('An error occured while retrieving cover note.');
                    return false;
                }
                
                //var privates = xml.getElementsByTagName('private');
                var names = xml.getElementsByTagName('name');
                var cover_notes = xml.getElementsByTagName('cover_note');
                
                $('name').value = names[0].childNodes[0].nodeValue;

                if (cover_notes[0].childNodes.length > 0) {
                    $('cover_note').value = cover_notes[0].childNodes[0].nodeValue;
                }
                
                /*if (privates[0].childNodes[0].nodeValue == 'Y') {
                    $('private').checked = true;
                } else {
                    $('private').checked = false;
                }*/
                
                $('div_resume_title').set('html', names[0].childNodes[0].nodeValue);
                set_status('');
            },
            onRequest: function(instance) {
                set_status('Retrieving cover note...');
            }
        });

        request.send(params);
    }
}

function remove_work_experience(index) {
    $('experience_form_' + index).setStyle('border', '3px dashed #FF0000');
    $('experience_form_' + index).setStyle('background-color', '#CCCCCC');
    
    if (!confirm("Are you sure to remove the selected record? \n\nClick Cancel if you want to keep the record.")) {
        $('experience_form_' + index).setStyle('border', 'none');
        $('experience_form_' + index).setStyle('background-color', '#FFFFFF');
        return false;
    }
    
    var resume_id = $('resume_id').value;
    
    if (work_experiences_count >= 0 && index >= 0) {
        if (work_experiences[index].record_id > 0) {
            var params = 'id=' + resume_id + '&member=' + id + '&action=delete_work_experience&experience=' + work_experiences[index].record_id;
            var uri = root + "/members/resume_action.php";
            var request = new Request({
                url: uri,
                method: 'post',
                onSuccess: function(txt, xml) {
                    if (txt == 'ko') {
                        set_status('An error occured while removing work experience.');
                        return false;
                    }
                    
                    set_status('');
                },
                onRequest: function(instance) {
                    set_status('Removing work experience...');
                }
            });

            request.send(params);
        } 
        
        work_experiences[index].deleted = true;
        $('experience_forms').removeChild($('experience_form_span_' + index));
    }
}

function got_retrenched(_was_retrenched, index) {
    if (_was_retrenched) {
        $('retrenched_hidden_' + index).value = 'retrenched';
    } else {
        $('retrenched_hidden_' + index).value = 'other';
    }
}

function add_work_experience(work_experience) {
    work_experiences_count++;
    var industry = '0';
    var from = new Array();
    var from_mm = '0';
    var from_yyyy = 'yyyy';
    var to = new Array();
    var to_mm = '0';
    var to_yyyy = 'yyyy';
    var el = document.createElement("span");
    el.id = "experience_form_span_" + work_experiences_count;
    
    if (work_experience == null) {
        work_experiences[work_experiences_count] = new WorkExperience(0, 0, "mm/yyyy", "mm/yyyy", "", "", "", "");
    } else {
        work_experiences[work_experiences_count] = work_experience;
        industry = work_experience.industry;
        
        if (work_experience.from != '') {
            if (work_experience.from.search('-') > 0) {
                from = work_experience.from.split('-');
                from_mm = from[1];
                from_yyyy = from[0];
            } else if (work_experience.from.search('/')) {
                from = work_experience.from.split('/');
                from_mm = from[0];
                from_yyyy = from[1];
            }
        }
        
        if (work_experience.to != '') {
            if (work_experience.to.search('-') > 0) {
                to = work_experience.to.split('-');
                to_mm = to[1];
                to_yyyy = to[0];
            } else if (work_experience.to.search('/')) {
                to = work_experience.to.split('/');
                to_mm = to[0];
                to_yyyy = to[1];
            }
        } 
    }

    var html = '<table id="experience_form_' + work_experiences_count + '" class="experiences_form">' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="label"><label for="industry_' + work_experiences_count + '">* Industry:</label></td>' + "\n";
    html = html + '<td class="field"><span id="industry_drop_down_' + work_experiences_count + '"></span></td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="label"><label for="from_' + work_experiences_count + '">* From:</label></td>' + "\n";
    html = html + '<td class="field"><span id="from_month_drop_down_' + work_experiences_count + '"></span><input class="year" type="text" id="from_year_' + work_experiences_count + '" name="from_year[]" value="' +  from_yyyy + '" maxlength="4" /></td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="label"><label for="to_' + work_experiences_count + '">To:</label></td>' + "\n";
    html = html + '<td class="field"><span id="to_month_drop_down_' + work_experiences_count + '"></span><input class="year" type="text" id="to_year_' + work_experiences_count + '" name="to_year[]" value="' +  to_yyyy + '" maxlength="4" /></td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="label"><label for="place_' + work_experiences_count + '">* Place:</label></td>' + "\n";
    html = html + '<td class="field"><input class="field" type="text" id="place_' + work_experiences_count + '" name="place[]" value="' +  work_experiences[work_experiences_count].place + '" /></td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="label"><label for="role_' + work_experiences_count + '">* Role:</label></td>' + "\n";
    html = html + '<td class="field"><input class="field" type="text" id="role_' + work_experiences_count + '" name="role[]" value="' +  work_experiences[work_experiences_count].role + '" /></td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="label"><label for="work_summary_' + work_experiences_count + '">* Work Summary:</label></td>' + "\n";
    html = html + '<td class="field"><textarea id="work_summary_' + work_experiences_count + '" name="work_summary[]">' +  work_experiences[work_experiences_count].work_summary + '</textarea></td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="label">Reason for Leaving:</td>' + "\n";
    html = html + '<td class="field">'; 
    var reason = '';
    var retrenched = false;
    var check_retrenched = '';
    var check_other = 'checked';
    var hidden = 'other'
    if (work_experiences[work_experiences_count].reason_for_leaving == 'retrenched') {
        reason = '';
        retrenched = true;
        check_retrenched = 'checked';
        check_other = '';
        hidden = 'retrenched'
    } else {
        reason = work_experiences[work_experiences_count].reason_for_leaving;
        retrenched = false;
        check_retrenched = '';
        check_other = 'checked';
        hidden = 'other';
    }
    html = html + '<input type="radio" id="retrenched_' + work_experiences_count + '" name="retrenched_' + work_experiences_count + '[]" value="retrenched" ' + check_retrenched + ' onClick="got_retrenched(true, ' + work_experiences_count + ');" />Retrenched<br/>';
    html = html + '<input type="radio" id="retrenched_' + work_experiences_count + '" name="retrenched_' + work_experiences_count + '[]" value="other" ' + check_other + ' onClick="got_retrenched(false, ' + work_experiences_count + ');" />Other Reason:&nbsp;<input style="width: 77%;" type="text" id="reason_for_leaving_' + work_experiences_count + '" name="reason_for_leaving[]" value="' + reason + '" />'; 
    html = html + '<input type="hidden" id="retrenched_hidden_' + work_experiences_count + '" name="retrenched_hidden_' + work_experiences_count + '" value="' + hidden + '" />';
    html = html + '</td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="buttons_left"><input class="button" type="button" value="Remove" onClick="remove_work_experience(' + work_experiences_count + ');"/></td>' + "\n";
    html = html + '<td class="buttons_right">&nbsp;</td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '</table>' + "\n";
    
    el.innerHTML = html;
    $('experience_forms').appendChild(el);
    
    list_industries_in(industry, "industry_drop_down_" + work_experiences_count, "industry_" + work_experiences_count, "industry[]");
    list_months_in(from_mm, "from_month_drop_down_" + work_experiences_count, "from_month_" + work_experiences_count, "from_month[]");
    list_months_in(to_mm, "to_month_drop_down_" + work_experiences_count, "to_month_" + work_experiences_count, "to_month[]");
}

function validate_work_experience_form(index) {
    var industry_dropdown = $('industry_' + index);
    var from_month = $('from_month_' + index);
    var from_year = $('from_year_' + index);
    var to_month = $('to_month_' + index);
    var to_year = $('to_year_' + index);
    var place = $('place_' + index);
    var role = $('role_' + index);
    var work_summary = $('work_summary_' + index);
    var retrenched = $('retrenched_hidden_' + index);
    var reason_for_leaving = $('reason_for_leaving_' + index);
    var date = new Date();
    var this_year = date.getFullYear();
    
    set_status('');
    industry_dropdown.style.borderColor = "";
    industry_dropdown.style.borderStyle = "";
    from_month.style.borderColor = "";
    from_month.style.borderStyle = "";
    from_year.style.borderColor = "";
    from_year.style.borderStyle = "";
    to_month.style.borderColor = "";
    to_month.style.borderStyle = "";
    to_year.style.borderColor = "";
    to_year.style.borderStyle = "";
    place.style.borderColor = "";
    place.style.borderStyle = "";
    role.style.borderColor = "";
    role.style.borderStyle = "";
    work_summary.style.borderColor = "";
    work_summary.style.borderStyle = "";
    
    if (industry_dropdown.options[industry_dropdown.selectedIndex].value == 0) {
        alert('Please select an industry.');
        industry_dropdown.style.border = "2px solid red";
        return false;
    }
    
    if (from_month.options[from_month.selectedIndex].value == 0) {
        alert('Please select the month the work started.');
        from_month.style.border = "2px solid red";
        return false;
    }
    
    if (isEmpty(from_year.value)) {
        alert('Please enter when the year the work experience began.');
        from_year.style.border = "2px solid red";
        return false;
    } else {
        if (from_year.value.length != 4) {
            alert('Please use the "yyyy" format.');
            from.style.border = "2px solid red";
            return false;
        }
        
        var yyyy = from_year.value;
        if (isNaN(yyyy) || parseInt(yyyy) > parseInt(this_year) || parseInt(yyyy) < (parseInt(this_year) - 65)) {
            alert('The year you entered is invalid.');
            from_year.style.border = "2px solid red";
            return false;
        }
    }
    
    if (!isEmpty(to_year.value) && to_year.value.toUpperCase() != 'YYYY') {
        if (to_month.options[to_month.selectedIndex].value == 0) {
            alert('Please select the month the work ended.');
            to_month.style.border = "2px solid red";
            return false;
        }
        
        if (to_year.value.length != 4) {
            alert('Please use the "yyyy" format.');
            to_year.style.border = "2px solid red";
            return false;
        }
        
        var yyyy = to_year.value;
        if (isNaN(yyyy) || parseInt(yyyy) > parseInt(this_year) || parseInt(yyyy) < (parseInt(this_year) - 65)) {
            alert('The year you entered is invalid.');
            to_year.style.border = "2px solid red";
            return false;
        }
    } else if (to_year.value.toUpperCase() == 'YYYY') {
        to_year.value = '';
    }
    
    if (isEmpty(place.value)) {
        alert('Please state the place where you used to work.');
        place.style.border = "2px solid red";
        return false;
    }
    
    if (isEmpty(role.value)) {
        alert('Please state the role/position.');
        role.style.border = "2px solid red";
        return false;
    }
    
    if (isEmpty(work_summary.value)) {
        alert('Please briefly summarize your work experience.');
        work_summary.style.border = "2px solid red";
        return false;
    }
    
    /*if (retrenched.value == 'other' && isEmpty(reason_for_leaving.value)) {
        alert('Please provide a reason why do you leave your job.');
        reason_for_leaving.style.border = "2px solid red";
        return false;
    }*/
    
    return true;
}

function save_work_experiences(_show_educations) {
    for (var i=0; i < work_experiences.length; i++) {
        if (!work_experiences[i].deleted) {
            if (!validate_work_experience_form(i)) {
                return false;
            }
        }
    }
    
    for (var i=0; i < work_experiences.length; i++) {
        if (!work_experiences[i].deleted) {
            var industry_dropdown = $('industry_' + i);
            var from_month = $('from_month_' + i);
            var from_year = $('from_year_' + i);
            var to_month = $('to_month_' + i);
            var to_year = $('to_year_' + i);
            var place = $('place_' + i);
            var role = $('role_' + i);
            var work_summary = $('work_summary_' + i);
            var retrenched = $('retrenched_hidden_' + i);
            var reason_for_leaving = $('reason_for_leaving_' + i);
            
            work_experiences[i].industry = industry_dropdown.options[industry_dropdown.selectedIndex].value;

            var from_mm = from_month.options[from_month.selectedIndex].value;
            var to_mm = "";
            if (to_month.options[to_month.selectedIndex].value > 0) {
                to_mm = to_month.options[to_month.selectedIndex].value;
            }
            
            work_experiences[i].from = from_mm + '/' + from_year.value;
            if (to_year.value != '' && parseInt(to_mm) > 0) {
                work_experiences[i].to = to_mm + '/' + to_year.value;
            } else {
                work_experiences[i].to = '';
            }
            work_experiences[i].place = place.value;
            work_experiences[i].role = role.value;
            work_experiences[i].work_summary = work_summary.value;
            
            if (retrenched.value == 'retrenched') {
                work_experiences[i].reason_for_leaving = 'retrenched';
            } else {
                work_experiences[i].reason_for_leaving = reason_for_leaving.value;
            }
            
            var resume_id = $('resume_id').value;
            var params = 'id=' + resume_id + '&member=' + id + '&action=save_work_experience';
            params = params + '&experience=' + work_experiences[i].record_id;
            params = params + '&industry=' + work_experiences[i].industry;
            params = params + '&from=' + work_experiences[i].from;
            params = params + '&to=' + work_experiences[i].to;
            params = params + '&place=' + work_experiences[i].place;
            params = params + '&role=' + work_experiences[i].role;
            params = params + '&work_summary=' + encodeURIComponent(work_experiences[i].work_summary);
            params = params + '&reason_for_leaving=' + encodeURIComponent(work_experiences[i].reason_for_leaving);
            
            var uri = root + "/members/resume_action.php";
            var request = new Request({
                url: uri,
                method: 'post',
                onSuccess: function(txt, xml) {
                    if (txt == 'ko') {
                        alert('An error occured while saving work experiences.');
                        return false;
                    }
                    
                    set_status('');
                },
                onRequest: function(instance) {
                    set_status('Saving work experiences...');
                }
            });

            request.send(params);
        }
    }
    
    if (_show_educations) {
        show_educations();
    }
}

function show_experiences() {
    if (is_new_resume) {
        alert('Please save the resume before proceeding.');
        return false;
    }
    
    selected_tab = 'li_experiences';
    $('li_cover_note').setStyle('border', '1px solid #0000FF');
    $('li_experiences').setStyle('border', '1px solid #CCCCCC');
    $('li_educations').setStyle('border', '1px solid #0000FF');
    $('li_skills').setStyle('border', '1px solid #0000FF');
    $('li_technical_skills').setStyle('border', '1px solid #0000FF');
    
    $('div_cover_note').setStyle('display', 'none');
    $('div_experiences').setStyle('display', 'block');
    $('div_educations').setStyle('display', 'none');
    $('div_skills').setStyle('display', 'none');
    $('div_technical_skills').setStyle('display', 'none');
    
    var resume_id = $('resume_id').value;
    if (resume_id > 0) {
        var work_experiences = new Array();
        work_experiences_count = -1;
        var params = 'id=' + resume_id + '&member=' + id + '&action=get_work_experiences';
        var uri = root + "/members/resume_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    set_status('An error occured while retrieving work experiences.');
                    return false;
                }
                
                var ids = xml.getElementsByTagName('id');
                var industries = xml.getElementsByTagName('industry');
                var froms = xml.getElementsByTagName('from');
                var tos = xml.getElementsByTagName('to');
                var places = xml.getElementsByTagName('place');
                var roles = xml.getElementsByTagName('role');
                var work_summaries = xml.getElementsByTagName('work_summary');
                var reason_for_leavings = xml.getElementsByTagName('reason_for_leaving');
                
                $('experience_forms').set('html', '');
                
                if (ids.length > 0) {
                    for(var i=0; i < ids.length; i++) {
                        var to = "";
                        var work_summary = "";
                        var reason_for_leaving = '';
                        
                        if (tos[i].childNodes[0] != null) {
                            to = tos[i].childNodes[0].nodeValue;
                        }
                        
                        if (work_summaries[i].childNodes[0] != null) {
                            work_summary = work_summaries[i].childNodes[0].nodeValue;
                        }
                        
                        if (reason_for_leavings[i].childNodes[0] != null) {
                            reason_for_leaving = reason_for_leavings[i].childNodes[0].nodeValue;
                        }
                        
                        work_experiences[i] = new WorkExperience(ids[i].childNodes[0].nodeValue, 
                                                                 industries[i].childNodes[0].nodeValue, 
                                                                 froms[i].childNodes[0].nodeValue, 
                                                                 to, 
                                                                 places[i].childNodes[0].nodeValue, 
                                                                 roles[i].childNodes[0].nodeValue, 
                                                                 work_summary, 
                                                                 reason_for_leaving);
                        
                        add_work_experience(work_experiences[i]);
                    }
                } else {
                    add_work_experience(null);
                }
                
                set_status('');
            },
            onRequest: function(instance) {
                set_status('Retrieving work experiences...');
            }
        });

        request.send(params);
    }
}

function remove_education(index) {
    $('education_form_' + index).setStyle('border', '3px dashed #FF0000');
    $('education_form_' + index).setStyle('background-color', '#CCCCCC');
    
    if (!confirm("Are you sure to remove the selected record? \n\nClick Cancel if you want to keep the record.")) {
        $('education_form_' + index).setStyle('border', 'none');
        $('education_form_' + index).setStyle('background-color', '#FFFFFF');
        return false;
    }
    
    var resume_id = $('resume_id').value;
    
    if (educations_count >= 0 && index >= 0) {
        if (educations[index].record_id > 0) {
            var params = 'id=' + resume_id + '&member=' + id + '&action=delete_education&education=' + educations[index].record_id;
            var uri = root + "/members/resume_action.php";
            var request = new Request({
                url: uri,
                method: 'post',
                onSuccess: function(txt, xml) {
                    if (txt == 'ko') {
                        set_status('An error occured while removing education.');
                        return false;
                    }
                    
                    set_status('');
                },
                onRequest: function(instance) {
                    set_status('Removing education...');
                }
            });

            request.send(params);
        } 
        
        educations[index].deleted = true;
        $('education_forms').removeChild($('education_form_span_' + index));
    }
}

function add_education(education) {
    educations_count++;
    var el = document.createElement("span");
    el.id = "education_form_span_" + educations_count;
    
    if (education == null) {
        educations[educations_count] = new Education(0, "", "", "", "");
    } else {
        educations[educations_count] = education;
    }

    var html = '<table id="education_form_' + educations_count + '" class="educations_form">' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="label"><label for="qualification_' + educations_count + '">* Qualification/Certification:</label></td>' + "\n";
    html = html + '<td class="field"><input class="field" type="text" id="qualification_' + educations_count + '" name="qualification[]" value="' +  educations[educations_count].qualification + '" /></td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="label"><label for="completed_on_' + educations_count + '">* Completion Year:</label></td>' + "\n";
    html = html + '<td class="field"><input class="year" type="text" id="completed_on_' + educations_count + '" name="completed_on[]" value="' +  educations[educations_count].completed_on + '" maxlength="4" /></td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="label"><label for="institution_' + educations_count + '">* Institution:</label></td>' + "\n";
    html = html + '<td class="field"><input class="field" type="text" id="institution_' + educations_count + '" name="institution[]" value="' +  educations[educations_count].institution + '" /></td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="label"><label for="country_' + educations_count + '">* Country:</label></td>' + "\n";
    html = html + '<td class="field"><span id="country_drop_down_' + educations_count + '"></span></td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="buttons_left"><input class="button" type="button" value="Remove" onClick="remove_education(' + educations_count + ');"/></td>' + "\n";
    html = html + '<td class="buttons_right">&nbsp;</td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '</table>' + "\n";
    
    el.innerHTML = html;
    $('education_forms').appendChild(el);
    
    list_all_countries_in(educations[educations_count].country, "country_drop_down_" + educations_count, "country_" + educations_count, "country[]");
}

function validate_education_form(index) {
    var country = $('country_' + index);
    var qualification = $('qualification_' + index);
    var completed_on = $('completed_on_' + index);
    var institution = $('institution_' + index);
    var date = new Date();
    var this_year = date.getFullYear();
    
    set_status('');
    country.style.borderColor = "";
    country.style.borderStyle = "";
    qualification.style.borderColor = "";
    qualification.style.borderStyle = "";
    completed_on.style.borderColor = "";
    completed_on.style.borderStyle = "";
    institution.style.borderColor = "";
    institution.style.borderStyle = "";
    
    if (isEmpty(qualification.value)) {
        alert('Please state the qualification/certification awarded.');
        qualification.style.border = "2px solid red";
        return false;
    }
    
    if (isEmpty(completed_on.value)) {
        alert('Please enter the year this qualification/certification was awarded.');
        completed_on.style.border = "2px solid red";
        return false;
    } else {
        var yyyy = completed_on.value;
        if (isNaN(yyyy) || parseInt(yyyy) < (parseInt(this_year) - 65)) {
            alert('The year you entered is invalid.');
            completed_on.style.border = "2px solid red";
            return false;
        }
    }
    
    if (isEmpty(institution.value)) {
        alert('Please state the institution where this qualification/certification was awarded.');
        institution.style.border = "2px solid red";
        return false;
    }
    
    if (country.options[country.selectedIndex].value == 0) {
        alert('Please select a country.');
        country.style.border = "2px solid red";
        return false;
    }
    
    return true;
}

function save_educations(_show_skills) {
    for (var i=0; i < educations.length; i++) {
        if (!educations[i].deleted) {
            if (!validate_education_form(i)) {
                return false;
            }
        }
    }
    
    for (var i=0; i < educations.length; i++) {
        if (!educations[i].deleted) {
            var country = $('country_' + i);
            var qualification = $('qualification_' + i);
            var completed_on = $('completed_on_' + i);
            var institution = $('institution_' + i);
            
            educations[i].country = country.options[country.selectedIndex].value;
            educations[i].completed_on = completed_on.value;
            educations[i].qualification = qualification.value;
            educations[i].institution = institution.value;

            var resume_id = $('resume_id').value;
            var params = 'id=' + resume_id + '&member=' + id + '&action=save_education';
            params = params + '&education=' + educations[i].record_id;
            params = params + '&country=' + educations[i].country;
            params = params + '&qualification=' + encodeURIComponent(educations[i].qualification);
            params = params + '&institution=' + educations[i].institution;
            params = params + '&completed_on=' + educations[i].completed_on;
            
            var uri = root + "/members/resume_action.php";
            var request = new Request({
                url: uri,
                method: 'post',
                onSuccess: function(txt, xml) {
                    if (txt == 'ko') {
                        alert('An error occured while saving educations.');
                        return false;
                    }

                    set_status('');
                },
                onRequest: function(instance) {
                    set_status('Saving educations...');
                }
            });

            request.send(params);
        }
    }
    
    if (_show_skills) {
        show_skills();
    }
}

function show_educations() {
    if (is_new_resume) {
        alert('Please save the resume before proceeding.');
        return false;
    }
    
    selected_tab = 'li_educations';
    $('li_cover_note').setStyle('border', '1px solid #0000FF');
    $('li_experiences').setStyle('border', '1px solid #0000FF');
    $('li_educations').setStyle('border', '1px solid #CCCCCC');
    $('li_skills').setStyle('border', '1px solid #0000FF');
    $('li_technical_skills').setStyle('border', '1px solid #0000FF');
    
    $('div_cover_note').setStyle('display', 'none');
    $('div_experiences').setStyle('display', 'none');
    $('div_educations').setStyle('display', 'block');
    $('div_skills').setStyle('display', 'none');
    $('div_technical_skills').setStyle('display', 'none');
    
    var resume_id = $('resume_id').value;
    if (resume_id > 0) {
        var educations = new Array();
        educations_count = -1;
        var params = 'id=' + resume_id + '&member=' + id + '&action=get_educations';
        var uri = root + "/members/resume_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    set_status('An error occured while retrieving educations.');
                    return false;
                }
                
                var ids = xml.getElementsByTagName('id');
                var qualifications = xml.getElementsByTagName('qualification');
                var completed_ons = xml.getElementsByTagName('completed_on');
                var institutions = xml.getElementsByTagName('institution');
                var countries = xml.getElementsByTagName('country');
                
                $('education_forms').set('html', '');
                
                if (ids.length > 0) {
                    for(var i=0; i < ids.length; i++) {
                        educations[i] = new Education(ids[i].childNodes[0].nodeValue, 
                                                      qualifications[i].childNodes[0].nodeValue, 
                                                      completed_ons[i].childNodes[0].nodeValue, 
                                                      institutions[i].childNodes[0].nodeValue, 
                                                      countries[i].childNodes[0].nodeValue);
                        
                        add_education(educations[i]);
                    }
                } else {
                    add_education(null);
                }
                
                set_status('');
            },
            onRequest: function(instance) {
                set_status('Retrieving educations...');
            }
        });

        request.send(params);
    }
}

function save_skills(_show_technical_skills) {
    if ((skill_sets.record_id == 0 && !isEmpty($('skills').value)) || 
        (skill_sets.record_id > 0 && (!isEmpty($('skills').value) || isEmpty($('skills').value)))) {
            var skills = $('skills').value;
            var resume_id = $('resume_id').value;
            var params = 'id=' + resume_id + '&member=' + id + '&action=save_skills';
            params = params + '&skill=' + skill_sets.record_id;
            params = params + '&skills=' + encodeURIComponent(skills);
            
            var uri = root + "/members/resume_action.php";
            var request = new Request({
                url: uri,
                method: 'post',
                onSuccess: function(txt, xml) {
                    if (txt == 'ko') {
                        alert('An error occured while saving skills.');
                        return false;
                    }

                    set_status('');
                    
                    if (_show_technical_skills) {
                        show_technical_skills();
                    }
                },
                onRequest: function(instance) {
                    set_status('Saving skills...');
                }
            });

            request.send(params);
    } else {
        set_status('Please enter the skills you have acquired, and separate them by commas.');
    }
}

function show_skills() {
    if (is_new_resume) {
        alert('Please save the resume before proceeding.');
        return false;
    }
    
    selected_tab = 'li_skills';
    $('li_cover_note').setStyle('border', '1px solid #0000FF');
    $('li_experiences').setStyle('border', '1px solid #0000FF');
    $('li_educations').setStyle('border', '1px solid #0000FF');
    $('li_skills').setStyle('border', '1px solid #CCCCCC');
    $('li_technical_skills').setStyle('border', '1px solid #0000FF');
    
    $('div_cover_note').setStyle('display', 'none');
    $('div_experiences').setStyle('display', 'none');
    $('div_educations').setStyle('display', 'none');
    $('div_skills').setStyle('display', 'block');
    $('div_technical_skills').setStyle('display', 'none');
    
    var resume_id = $('resume_id').value;
    if (resume_id > 0) {
        var params = 'id=' + resume_id + '&member=' + id + '&action=get_skills';
        var uri = root + "/members/resume_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    set_status('An error occured while retrieving skills.');
                    return false;
                }
                
                var ids = xml.getElementsByTagName('id');
                var skills = xml.getElementsByTagName('skill');
                
                $('skills').value = '';
                if (ids.length > 0) {
                    if (skills[0].childNodes.length > 0) {
                        $('skills').value = decodeURIComponent(skills[0].childNodes[0].nodeValue);
                        skill_sets = new SkillSets(ids[0].childNodes[0].nodeValue, 
                                                   skills[0].childNodes[0].nodeValue);
                    } else {
                        skill_sets = new SkillSets(ids[0].childNodes[0].nodeValue, '');
                    }
                } else {
                    skill_sets = new SkillSets(0, '');
                }
                
                set_status('');
            },
            onRequest: function(instance) {
                set_status('Retrieving skills...');
            }
        });

        request.send(params);
    }
}

function remove_technical_skill(index) {
    $('technical_skill_form_' + index).setStyle('border', '3px dashed #FF0000');
    $('technical_skill_form_' + index).setStyle('background-color', '#CCCCCC');
    
    if (!confirm("Are you sure to remove the selected record? \n\nClick Cancel if you want to keep the record.")) {
        $('technical_skill_form_' + index).setStyle('border', 'none');
        $('technical_skill_form_' + index).setStyle('background-color', '#FFFFFF');
        return false;
    }
    
    var resume_id = $('resume_id').value;
    
    if (technical_skills_count >= 0 && index >= 0) {
        if (technical_skills[index].record_id > 0) {
            var params = 'id=' + resume_id + '&member=' + id + '&action=delete_technical_skill&technical_skill=' + technical_skills[index].record_id;
            var uri = root + "/members/resume_action.php";
            var request = new Request({
                url: uri,
                method: 'post',
                onSuccess: function(txt, xml) {
                    if (txt == 'ko') {
                        set_status('An error occured while removing technical skill.');
                        return false;
                    }
                    
                    set_status('');
                },
                onRequest: function(instance) {
                    set_status('Removing technical skill...');
                }
            });

            request.send(params);
        } 
        
        technical_skills[index].deleted = true;
        $('technical_skill_forms').removeChild($('technical_skill_form_span_' + index));
    }
}

function add_technical_skill(technical_skill) {
    technical_skills_count++;
    var el = document.createElement("span");
    el.id = "technical_skill_form_span_" + technical_skills_count;
    
    if (technical_skill == null) {
        technical_skills[technical_skills_count] = new TechnicalSkill(0, "", "");
    } else {
        technical_skills[technical_skills_count] = technical_skill;
    }

    var html = '<table id="technical_skill_form_' + technical_skills_count + '" class="technical_skills_form">' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="label"><label for="technical_skill_' + technical_skills_count + '">Technical Skill:</label></td>' + "\n";
    html = html + '<td class="field"><input class="field" type="text" id="technical_skill_' + technical_skills_count + '" name="technical_skill[]" value="' +  technical_skills[technical_skills_count].technical_skill + '" /></td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="label"><label for="level_' + technical_skills_count + '">Level:</label></td>' + "\n";
    html = html + '<td class="field"><span id="level_drop_down_' + technical_skills_count + '"></span></td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '<tr>' + "\n";
    html = html + '<td class="buttons_left"><input class="button" type="button" value="Remove" onClick="remove_technical_skill(' + technical_skills_count + ');"/></td>' + "\n";
    html = html + '<td class="buttons_right">&nbsp;</td>' + "\n";
    html = html + '</tr>' + "\n";
    html = html + '</table>' + "\n";
    
    el.innerHTML = html;
    $('technical_skill_forms').appendChild(el);
    
    list_skill_levels_in(technical_skills[technical_skills_count].level, "level_drop_down_" + technical_skills_count, "level_" + technical_skills_count, "level[]");
}

function validate_technical_skill_form(index) {
    var technical_skill = $('technical_skill_' + index);
    var level = $('level_' + index);
    
    set_status('');
    technical_skill.style.borderColor = "";
    technical_skill.style.borderStyle = "";
    level.style.borderColor = "";
    level.style.borderStyle = "";
    
    if (isEmpty(technical_skill.value)) {
        alert('Please state the technical skill.');
        technical_skill.style.border = "2px solid red";
        return false;
    }
    
    if (level.options[level.selectedIndex].value == 0) {
        alert('Please select a level for the stated technical skill.');
        level.style.border = "2px solid red";
        return false;
    }
    
    return true;
}

function save_technical_skills(_finished) {
    for (var i=0; i < technical_skills.length; i++) {
        if (!technical_skills[i].deleted) {
            if ($('level_' + i).options[$('level_' + i).selectedIndex].value != 0 || !isEmpty($('technical_skill_' + i).value)) {
                if (!validate_technical_skill_form(i)) {
                    return false;
                }
            }
        }
    }
    
    for (var i=0; i < technical_skills.length; i++) {
        if (!technical_skills[i].deleted) {
            var technical_skill = $('technical_skill_' + i);
            var level = $('level_' + i);
            
            if (level.options[level.selectedIndex].value != 0 || !isEmpty(technical_skill.value)) {
                technical_skills[i].technical_skill = technical_skill.value;
                technical_skills[i].level = level.options[level.selectedIndex].value;
                
                var resume_id = $('resume_id').value;
                var params = 'id=' + resume_id + '&member=' + id + '&action=save_technical_skill';
                params = params + '&technical_skill=' + technical_skills[i].record_id;
                params = params + '&skill=' + encodeURIComponent(technical_skills[i].technical_skill);
                params = params + '&level=' + technical_skills[i].level;
                
                var uri = root + "/members/resume_action.php";
                var request = new Request({
                    url: uri,
                    method: 'post',
                    onSuccess: function(txt, xml) {
                        if (txt == 'ko') {
                            alert('An error occured while saving technical skills.');
                            return false;
                        }

                        set_status('');
                    },
                    onRequest: function(instance) {
                        set_status('Saving technical skills...');
                    }
                });

                request.send(params);
            }
        }
    }
    
    if (_finished) {
        show_resumes();
    }
}

function show_technical_skills() {
    if (is_new_resume) {
        alert('Please save the resume before proceeding.');
        return false;
    }
    
    selected_tab = 'li_technical_skills';
    $('li_cover_note').setStyle('border', '1px solid #0000FF');
    $('li_experiences').setStyle('border', '1px solid #0000FF');
    $('li_educations').setStyle('border', '1px solid #0000FF');
    $('li_skills').setStyle('border', '1px solid #0000FF');
    $('li_technical_skills').setStyle('border', '1px solid #CCCCCC');
    
    $('div_cover_note').setStyle('display', 'none');
    $('div_experiences').setStyle('display', 'none');
    $('div_educations').setStyle('display', 'none');
    $('div_skills').setStyle('display', 'none');
    $('div_technical_skills').setStyle('display', 'block');
    
    var resume_id = $('resume_id').value;
    if (resume_id > 0) {
        var technical_skills = new Array();
        technical_skills_count = -1;
        var params = 'id=' + resume_id + '&member=' + id + '&action=get_technical_skills';
        var uri = root + "/members/resume_action.php";
        var request = new Request({
            url: uri,
            method: 'post',
            onSuccess: function(txt, xml) {
                if (txt == 'ko') {
                    set_status('An error occured while retrieving technical skills.');
                    return false;
                }
                
                var ids = xml.getElementsByTagName('id');
                var skills = xml.getElementsByTagName('technical_skill');
                var levels = xml.getElementsByTagName('level');
                
                $('technical_skill_forms').set('html', '');
                
                if (ids.length > 0) {
                    for(var i=0; i < ids.length; i++) {
                        technical_skills[i] = new TechnicalSkill(ids[i].childNodes[0].nodeValue, 
                                                                 skills[i].childNodes[0].nodeValue, 
                                                                 levels[i].childNodes[0].nodeValue);
                        
                        add_technical_skill(technical_skills[i]);
                    }
                } else {
                    add_technical_skill(null);
                }
                
                set_status('');
            },
            onRequest: function(instance) {
                set_status('Retrieving technical skills...');
            }
        });

        request.send(params);
    }
}

function onDomReady() {
    set_root();
    get_employers_for_mini();
    get_industries_for_mini();
    set_mini_keywords();
    get_referrals_count();
    get_requests_count();
    get_jobs_employed_count();
    set_mouse_events();
    
    $('add_new_resume').addEvent('click', add_new_resume);
    $('add_new_resume_1').addEvent('click', add_new_resume);
    $('upload_new_resume').addEvent('click', upload_new_resume);
    $('upload_new_resume_1').addEvent('click', upload_new_resume);
    
    $('li_resumes').addEvent('click', show_resumes);
    $('li_cover_note').addEvent('click', show_cover_note);
    $('li_experiences').addEvent('click', show_experiences);
    $('li_educations').addEvent('click', show_educations);
    $('li_skills').addEvent('click', show_skills);
    $('li_technical_skills').addEvent('click', show_technical_skills);
    
    $('sort_name').addEvent('click', function() {
        order_by = 'name';
        ascending_or_descending();
        show_resumes();
    });
    
    $('sort_modified_on').addEvent('click', function() {
        order_by = 'modified_on';
        ascending_or_descending();
        show_resumes();
    });
    
    show_resumes();
    
    var suggest_url = root + '/common/php/search_suggest.php';
    new Autocompleter.Ajax.Json('mini_keywords', suggest_url, {
        'postVar': 'keywords',
        'minLength' : 1,
        'overflow' : true,
        'delay' : 50
    });
}

window.addEvent('domready', onDomReady);
