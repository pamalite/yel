function linkedin_authorize() {
    IN.User.refresh();
}

function logout_from_linkedin() {
    IN.User.logout(function() {}, window);
}