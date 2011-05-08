function show_tip(_tip) {
    switch (_tip) {
        case 'reward':
            $('tip_title').set('html', 'Recommender\'s Reward');
            $('tip_content').set('html', 'We will reward you the cash amount stated in this job description once any of the candidates that you have recommended to us is successfully hired for this job position.');
            break;
        case 'bonus':
            $('tip_title').set('html', 'Candidate\'s Bonus');
            $('tip_content').set('html', 'This is a congratulatory gift from us and a token of appreciation for using YellowElevator.com. When you are successfully hired for this job position, please sign into your account and click the "I\'m Employed" button located in the Job Applications section in order to claim your Candidate\'s Bonus.');
            break;            
    }
    
    show_window('tip_window');
}