var order_by = 'modified_on';
var order = 'desc';

function ascending_or_descending() {
    if (order == 'desc') {
        order = 'asc';
    } else {
        order = 'desc';
    }
}

function sort_by(_table, _column) {
    order_by = _column;
    ascending_or_descending();
    show_resumes();
}

function show_resumes() {
    var params = 'id=' + id + '&action=get_resumes';
    params = params + '&order_by=' + order_by + ' ' + order;
    
    var uri = root + "/members/resumes_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            set_status('');
            
            if (txt == 'ko') {
                alert('An error occured while loading resumes.');
                return false;
            }
            
            if (txt == '0') {
               $('div_resumes').set('html', '<div class="empty_results">No resumes uploaded. Click &quot;Upload Resume&quot; button to upload one now.</div>');
            } else {
                var ids = xml.getElementsByTagName('id');
                var file_names = xml.getElementsByTagName('file_name');
                var modified_ons = xml.getElementsByTagName('formatted_modified_on');
                
                var resumes_table = new FlexTable('resumes_table', 'payments');
                
                var header = new Row('');
                header.set(0, new Cell("<a class=\"sortable\" onClick=\"sort_by('resumes', 'modified_on');\">Modified On</a>", '', 'header'));
                header.set(1, new Cell("<a class=\"sortable\" onClick=\"sort_by('resumes', 'file_name');\">Resume</a>", '', 'header'));
                header.set(2, new Cell("&nbsp;", '', 'header actions'));
                resumes_table.set(0, header);
                
                for (var i=0; i < ids.length; i++) {
                    var row = new Row('');
                    
                    row.set(0, new Cell(modified_ons[i].childNodes[0].nodeValue, '', 'cell'));
                    row.set(1, new Cell('<a href="resume.php?id=' + ids[i].childNodes[0].nodeValue + '">' + file_names[i].childNodes[0].nodeValue + '</a>', '', 'cell'));
                    ow.set(2, new Cell('<a class="no_link" onClick="update_resume('+ ids[i].childNodes[0].nodeValue + ');">Update</a>', '', 'cell actions'));
                    //row.set(2, new Cell('<a class="no_link" onClick="delete_resume(' + ids[i].childNodes[0].nodeValue + ');">Delete</a>&nbsp;|&nbsp;<a class="no_link" onClick="update_resume('+ ids[i].childNodes[0].nodeValue + ');">Update</a>', '', 'cell actions'));
                    resumes_table.set((parseInt(i)+1), row);
                }
                
                $('div_resumes').set('html', resumes_table.get_html());
            }
        },
        onRequest: function(instance) {
            set_status('Loading resumes...');
        }
    });
    
    request.send(params);
}

function update_resume(_resume_id) {
    show_upload_resume_popup(_resume_id);
}

function close_upload_resume_popup(_is_upload) {
    if (_is_upload) {
        if (isEmpty($('my_file').value)) {
            alert('You need to select a resume to upload.');
            return false;
        }
        
        $('upload_resume_form').submit();
        start_upload();
    } else {
        close_window('upload_resume_window');
    }
}

function show_upload_resume_popup(_resume_id) {
    $('resume_id').value = _resume_id;
    show_window('upload_resume_window');
    window.scrollTo(0, 0);
}

function start_upload() {
    $('upload_progress').setStyle('display', 'block');
    $('upload_field').setStyle('display', 'none');
    return true;
}

function stop_upload(_success) {
    var result = '';
    $('upload_progress').setStyle('display', 'none');
    if (_success == 1) {
        close_window('upload_resume_window');
        show_resumes();
        return true;
    } else {
        alert('An error occured while uploading your resume. Make sure your resume file meets the conditions stated.');
        return false;
    }
}

function onDomReady() {
    set_root();
    
    /*var suggest_url = root + '/common/php/search_suggest.php';
    new Autocompleter.Ajax.Json('mini_keywords', suggest_url, {
        'postVar': 'keywords',
        'minLength' : 1,
        'overflow' : true,
        'delay' : 50
    });*/
}

window.addEvent('domready', onDomReady);
