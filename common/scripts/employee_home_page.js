function onDomReady() {
    set_root();
    get_unapproved_photos_count();
    get_employee_rewards_count();
    get_employee_tokens_count();
}

window.addEvent('domready', onDomReady);
