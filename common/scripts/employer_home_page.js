function show_invoice_page(invoice_id) {
    var popup = window.open('invoice.php?id=' + invoice_id, '', 'scrollbars');
    
    if (!popup) {
        alert('Popup blocker was detected. Please allow pop-up windows for YellowElevator.com and try again.');
    }
}

function show_referred_jobs() {
    var params = 'id=' + id + '&action=get_recent_referrals';
    
    var uri = root + "/employers/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading referred jobs.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">There are no referrals provided by the referrers.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var industries = xml.getElementsByTagName('industry');
                var titles = xml.getElementsByTagName('title');
                var created_ons = xml.getElementsByTagName('formatted_created_on');
                var expire_ons = xml.getElementsByTagName('formatted_expire_on');
                var referrals = xml.getElementsByTagName('num_referrals');
                var descriptions = xml.getElementsByTagName('description');
                
                for (var i=0; i < ids.length; i++) {
                    var job_id = ids[i];

                    html = html + '<tr id="'+ job_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";

                    html = html + '<td class="industry">' + industries[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="title">' + titles[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + created_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + expire_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="referrals"><a href="referrals.php?job=' + job_id.childNodes[0].nodeValue + '">' + referrals[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_referred_jobs_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            var html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">Loading currently referred jobs...</div>';
            $('div_referred_jobs_list').set('html', html);
        }
    });
    
    request.send(params);
}

function show_new_invoices() {
    var params = 'id=' + id + '&action=get_new_invoices';
    
    var uri = root + "/employers/home_action.php";
    var request = new Request({
        url: uri,
        method: 'post',
        onSuccess: function(txt, xml) {
            if (txt == 'ko') {
                set_status('An error occured while loading new invoices.');
                return false;
            }
            
            var html = '<table id="list" class="list">';
            if (txt == '0') {
                html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">No new invoices.</div>';
            } else {
                var ids = xml.getElementsByTagName('id');
                var padded_ids = xml.getElementsByTagName('padded_id');
                var types = xml.getElementsByTagName('type');
                var payable_bys = xml.getElementsByTagName('formatted_payable_by');
                var expireds = xml.getElementsByTagName('expired');
                var issued_ons = xml.getElementsByTagName('formatted_issued_on');
                
                for (var i=0; i < ids.length; i++) {
                    var invoice_id = ids[i];
                    
                    html = html + '<tr id="'+ invoice_id.childNodes[0].nodeValue + '" onMouseOver="this.style.backgroundColor = \'#FFFF00\';" onMouseOut="this.style.backgroundColor = \'#FFFFFF\';">' + "\n";
                    
                    if (expireds[i].childNodes[0].nodeValue == 'expired') {
                        //html = html + '<td class="expired" style="background-color: #FF0000;">&nbsp;</td>' + "\n";
                        html = html + '<td class="expired"><img class="warning" src="' + root + '/common/images/icons/expired.png" /></td>' + "\n";
                    } else if (expireds[i].childNodes[0].nodeValue == 'nearly') {
                        //html = html + '<td class="expired" style="background-color: #FFFF00;">&nbsp;</td>' + "\n";
                        html = html + '<td class="expired"><img class="warning" src="' + root + '/common/images/icons/just_expired.png" /></td>' + "\n";
                    } else {
                        html = html + '<td class="expired">&nbsp;</td>' + "\n";
                    }
                    
                    html = html + '<td class="date">' + issued_ons[i].childNodes[0].nodeValue + '</td>' + "\n";
                    html = html + '<td class="date">' + payable_bys[i].childNodes[0].nodeValue + '</td>' + "\n";

                    var type = 'Other';
                    switch (types[i].childNodes[0].nodeValue) {
                        case 'R':
                            type = 'Reference';
                            break;
                        case 'J':
                            type = 'Job';
                            break;
                    }

                    html = html + '<td class="type">' + type + '</td>' + "\n";
                    html = html + '<td class="invoice"><a class="no_link" onClick="show_invoice_page(\'' + invoice_id.childNodes[0].nodeValue + '\')">' + padded_ids[i].childNodes[0].nodeValue + '</a></td>' + "\n";
                    html = html + '</tr>' + "\n";
                }
                html = html + '</table>';
            }
            
            $('div_new_invoices_list').set('html', html);
            set_status('');
        },
        onRequest: function(instance) {
            var html = '<div style="text-align: center; padding-top: 10px; padding-bottom: 10px;">Loading new invoices.</div>';
            $('div_new_invoices_list').set('html', html);
        }
    });
    
    request.send(params);
}

function onDomReady() {
    set_root();
    get_employer_referrals_count();
    //show_referred_jobs();
    //show_new_invoices();
}

window.addEvent('domready', onDomReady);
