/**
 * Display Map
 *
 * @param geo_info
 */
function bblog_display_map( geo_info ) {

    for ( var i =0; i<geo_info.length; i++ ) {
        bblog_init_map(geo_info[i]);
    }
}

/**
 * Display editable map
 */
function bblog_init_editable_map() {
	
	//create map
	
	//we may be creating or editing post
	//in case of editing post, we need to set the location and geo lat lang?
	var location = jQuery('#custom-field-_location').val();//based on the key we registered earlier
	
	var lat = jQuery('#custom-field-_geo_lat').val();
	var lng =  jQuery('#custom-field-_geo_lng').val();
	//default position, chandigarh
	var position = {lat:  30.7204507, lng: 76.7669704};

	if ( lat !='' && lng != '' ) {
		//just converting to float
		position = {lat: lat - 0 , lng: lng - 0 };
	}
	
	var map = new google.maps.Map(document.getElementById('bblog-map-edit-canvas'), {
	  center: position ,
	  zoom: 4,
	  mapTypeId: google.maps.MapTypeId.ROADMAP
	});


	var input = (document.getElementById('pac-input'));
	
	jQuery(input).val( location );
	
	//var types = document.getElementById('type-selector');
	map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
  
	//map.controls[google.maps.ControlPosition.TOP_LEFT].push(types);
  
	var autocomplete = new google.maps.places.Autocomplete(input);
	autocomplete.bindTo('bounds', map);
  
	var infowindow = new google.maps.InfoWindow();
	
	var marker = new google.maps.Marker({
	  map: map,
	  anchorPoint: new google.maps.Point(0, -29)
	});

	if ( lat !='') {
		marker.setPosition(position);
	}
  
	autocomplete.addListener('place_changed', function() {
		infowindow.close();
		marker.setVisible( false );
		var place = autocomplete.getPlace();
		
		if ( ! place.geometry ) {
		  //window.alert("Autocomplete's returned place contains no geometry");
		  return;
		}

		map.setCenter(place.geometry.location);
		map.setZoom(13);  // zoooming it

		marker.setPosition( place.geometry.location );
		marker.setVisible(true);

		var text = jQuery(input).val();

		//console.log(text);

		infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + text);
		infowindow.open(map, marker);
		
		jQuery('#custom-field-_location').val(text);
		jQuery('#custom-field-_geo_lat').val(place.geometry.location.lat());
		jQuery('#custom-field-_geo_lng').val(place.geometry.location.lng());

  });
}


function bblog_init_map( info ) {
	//create map
	var latLang = {lat:  info.lat -0 , lng: info.lng-0 };

	var map = new google.maps.Map(document.getElementById('bblog-map-canvas-'+info.id), {
	  zoom: 7,
		center: latLang
	  
	});

    var marker = new google.maps.Marker({
	  map: map,
	  position: latLang,
	  title: info.location
	});

    //var info_window = new google.maps.InfoWindow();
	//infowindow.setContent('<div><strong>' + info.location + '</strong><br>');
	//infowindow.open(map, marker);

}

jQuery( document ).ready( function() {

	//initialize all maps
	//if we are on edit page, let us provide the UI for editing
	
	if ( jQuery('#bblog-map-edit-canvas').get(0) ) {
		bblog_init_editable_map();
	}

	if (  typeof bblog_posts_geo !== 'undefined' ) {
		bblog_posts_geo = JSON.parse(bblog_posts_geo);
		bblog_display_map( bblog_posts_geo );
	}
});