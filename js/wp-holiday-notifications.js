function setWhnCookie(cname, cvalue) {
  const d = new Date();
  d.setTime(d.getTime() + (1*24*60*60*1000));
  let expires = "expires="+ d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
function getWhnCookie(cname) {
  let name = cname + "=";
  let decodedCookie = decodeURIComponent(document.cookie);
  let ca = decodedCookie.split(';');
  for(let i = 0; i <ca.length; i++) {
	let c = ca[i];
	while (c.charAt(0) == ' ') {
	  c = c.substring(1);
	}
	if (c.indexOf(name) == 0) {
	  return c.substring(name.length, c.length);
	}
  }
  return "";
}
jQuery(document).ready(function(){
	jQuery('.whn-close').click(function(){
		jQuery(this).closest('.whn-modal').hide();
	});
});
jQuery( window ).on("load", function() {
	jQuery('.whn-modal').each(function(i, obj) {
		var _id = jQuery(this).attr('data-id');
		var _is = getWhnCookie('whn-md-'+ _id);
		if(_is != 1){
			jQuery('.whn-modal').show();
			setWhnCookie('whn-md-'+ _id, 1);
		}
	});
});
