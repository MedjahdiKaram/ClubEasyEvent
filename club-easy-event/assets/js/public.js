(function($){
'use strict';

function getNonce($button){
if ($button.data('nonce')) {
return $button.data('nonce');
}
return (window.CEE_Public && CEE_Public.nonce) ? CEE_Public.nonce : '';
}

$(document).on('click', '.cee-rsvp-button', function(){
var $button = $(this);
var $container = $button.closest('.cee-event-rsvp');
var eventId = $container.data('event-id');
var response = $button.data('response');
var nonce = getNonce($button);

if (!eventId || !response || !nonce) {
return;
}

$container.find('.cee-rsvp-button').removeClass('is-active');
$button.addClass('is-active');
$container.find('.cee-rsvp-status').text('…');

$.ajax({
url: (window.CEE_Public && CEE_Public.ajax_url) ? CEE_Public.ajax_url : window.ajaxurl,
type: 'POST',
dataType: 'json',
data: {
action: 'cee_handle_rsvp',
event_id: eventId,
response: response,
nonce: nonce
}
}).done(function(resp){
if (resp && resp.success) {
$container.find('.cee-rsvp-status').text(CEE_Public && CEE_Public.i18n ? CEE_Public.i18n.success : '');
} else {
$container.find('.cee-rsvp-status').text(CEE_Public && CEE_Public.i18n ? CEE_Public.i18n.error : '');
$button.removeClass('is-active');
}
}).fail(function(){
$container.find('.cee-rsvp-status').text(CEE_Public && CEE_Public.i18n ? CEE_Public.i18n.error : '');
$button.removeClass('is-active');
});
});
})(jQuery);
