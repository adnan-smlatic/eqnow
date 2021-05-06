var lightMap = L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}',{
    id: 'mapbox/light-v10',
    tileSize: 512,
    zoomOffset: -1, // offsetting tile size
    accessToken: '{{ACCESS TOKEN}}'
});

var satMap = L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}',{
  id: 'mapbox/satellite-v9',
  tileSize: 512,
  zoomOffset: -1,
  accessToken: '{{ACCESS TOKEN}}'
});
var darkMap = L. tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}',{
  id: 'mapbox/dark-v10',
  tileSize: 512,
  zoomOffset: -1,
  accessToken: '{{ACCESS TOKEN}}',
  detectRetina: true
});

var faultsURL = "https://raw.githubusercontent.com/fraxen/tectonicplates/master/GeoJSON/PB2002_boundaries.json"

//objects for tectonic fault layer, base map and overlay layers
var faults = new L.layerGroup();
var baseMaps = {
  "Light": lightMap,
  "Dark": darkMap,
  "Satellite": satMap
};

var overlayMaps = {
  "Tectonic plates": faults
};

//creates map + properties and default
if (earthquakes != null){
  var latM = earthquakes[0][1];
  var lonM = earthquakes[0][2];
}else{
  var latM = 45;
  var lonM = 45;
}

var mymap = L.map("mapid", {
    center: [latM, lonM],
    zoom: 3,
    maxZoom: 10,
    minZoom: 1,
    worldCopyJump: true,
    layers: [darkMap]
}); 


// adds tectonic plate data
d3.json(faultsURL, function(plateData) {
  // add geojson data
  L.geoJson(plateData, {
    color: "orange",
    weight: 2
  })
  .addTo(faults);
});


// Add the layer control to the map
L.control.layers(baseMaps, overlayMaps, {
  collapsed: false
}).addTo(mymap);



//show popup on map click
var popup = L.popup();
function onMapClick(e) {
    $.getJSON('https://www.seismicportal.eu/fe_regions_ws/query?format=json&lat='+e.latlng.lat+'&lon='+e.latlng.lng+'', function(a) {
      popup
        .setLatLng(e.latlng)
        .setContent('Region: '+ a['name_l'] +'<br>Latitude: '+e.latlng.lat.toFixed(2)+'<br>Longitude: '+e.latlng.lng.toFixed(2)+'<br> <a class="btn btn-info" href="index.php?start=&end=&minmag=&maxmag=&magtype=&mindepth=&maxdepth=&orderby=time&minlat=&maxlat=&minlon=&maxlon=&lat='+
        e.latlng.lat.toFixed(2)+'&lon='+e.latlng.lng.toFixed(2)+'&minradius=&maxradius=2&submitCustomFilter=" role="button" style="color: white;">Earthquakes in this area <i class="bi bi-search"></i></a>')
        .openOn(mymap);
      
    });
}
mymap.on('click', onMapClick); 

//add map scale
L.control.scale({imperial: false}).addTo(mymap);

//add legend
var legend = L.control({ position: 'bottomright' });
legend.onAdd = function (mymap) {

  var div = L.DomUtil.create('div', 'legend');
  labels = ['<strong>Magnitude scale</strong>'],
    grades = ["#ff2800", "#C80000", "#800000", "#FF9100", "#1b8700f1", "#03afff", "#A2A2A2"],
    categories = ["Great", "Major", "Strong", "Moderate", "Light", "Minor", "Micro"];

  for (var i = 0; i < categories.length; i++) {

    div.innerHTML +=
      labels.push(
        '<i class="circle" style="background:' + grades[i] + '"></i> ' +
        (categories[i] ? categories[i] : '+'));

  }
  div.innerHTML = labels.join('<br>');
  return div;
};
legend.addTo(mymap);

var markerGroup = L.layerGroup().addTo(mymap);

function getColor(mag) {
  return mag >= 8 ? ["#ff2600", "eqGreat"] :
    mag >= 7 ? ["#C80000", "eqMajor"] :
      mag >= 6 ? ["#800000", "eqStrong"] :
        mag >= 5 ? ["#FF9100", "eqModerate"] :
          mag >= 4 ? ["#1b8700f1", "eqLight"] :
            mag >= 2 ? ["#03afff", "eqMinor"] :
              ["#A2A2A2", "eqMicro"];
}
function getEarthquakes(earthquakes) {
  var onLoad = false;

  $(earthquakes).each(function () {
    var earthquake = $(this);
    var eventid = earthquake[0];

    var lat = earthquake[1];
    var lon = earthquake[2];
    var location = earthquake[3];
    var date = earthquake[4];
    var depth = earthquake[5] / 1000;
    var mag = earthquake[6];

    var markers = [];

    var circle = L.circle([lat, lon], {
      color: getColor(mag)[0],
      fillColor: getColor(mag)[0],
      fillOpacity: 0.7,
      radius: 3000 * mag,
      title: eventid
    }).addTo(markerGroup);

    circle.bindPopup('<p class="mb-1"><span class="fw-bolder">' + location + '</span><br>Magnitude: ' + mag + '<br>Date: ' + date +
      '<br>Depth: ' + depth + ' km <div class="text-center"><a class="btn btn-primary" style="color:white;" role="button" href="earthquake.php?id=' + eventid + '">Details</a></div></p>');

    markers.push(circle);

    //fly to x marker on map based on id from marker array
    function gotoMarker(id) {
      for (var i in markers) {
        var markerID = markers[i].options.title;
        if (markerID == id) {
          mymap.flyTo([lat, lon], 6, {
            animate: true,
            duration: 1
          });
          markers[i].openPopup();
        };
      }
    }

    //on click item from sidebar, go to it on map // remove and set active class
    $('.item').click(function () {
      gotoMarker($(this)[0].id);
      $(this).siblings('.item').removeClass('activeItem');
      $(this).addClass('activeItem');
    });

    //execute only on load once
    if (!onLoad) {
      markers[0].openPopup();
      onLoad = true;
    }
  });//end for each

}//end function


function clearMarkers() {
  markerGroup.clearLayers();
  $('.sidebarItems').empty();
  markers = [];
  
}


function sendNot() {
  function showNotification() {
    const notification = new Notification("New message kaegjkesgj", {
      body: "hey kjegokerolgkegkwlkgle",
      icon: "img/icon_eq.png"
    });
    notification.onclick = (e) =>{
      window.location.href="google.com";
    };
  }

  console.log(Notification.permission);

  if (Notification.permission === "granted") {
    showNotification();
    console.log("ran");
  } else if (Notification.permission !== "denied") {
    Notification.requestPermission().then(permission => {
      if (permission === "granted") {
        showNotification();
      }
    });
  }

}

// earthquakes toasts
var option = {
  animation: true,
  autohide: true,
  delay: 7000
}
//enable toasts
var toastElList = [].slice.call(document.querySelectorAll('.toast'))
var toastList = toastElList.map(function (toastEl) {
  return new bootstrap.Toast(toastEl, option)
})
var toastCounter = 0;
var sock = new SockJS('https://www.seismicportal.eu/standing_order');
sock.onopen = function() {

    console.log('connected');

};
sock.onmessage = function(e) {

    msg = JSON.parse(e.data);
    $('#toastContainer').append('<div id="toast'+toastCounter+'" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">'+
        '<div class="toast-header"> <img src="img/icon_eq.png" class="rounded me-2" alt="..."> <strong class="me-auto">Earthquake Now</strong><small>Just now</small>' +
        '<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button> </div> <div class="toast-body" style="color:black;">'+
        '<strong><span class="badge ' + getColor(msg["data"]["properties"]["mag"])[1]+'">'+ msg["data"]["properties"]["mag"] +
        '</span></strong> magnitude earthquake occured in '+msg["data"]["properties"]["flynn_region"]+
        '. <hr> <a type="button" role="button" class="btn btn-primary btn-sm" href="earthquake.php?id='+msg["data"]["id"]+'">Earthquake details</a></div></div>');

    var toastHTML = document.getElementById('toast'+toastCounter);
    var toast = new bootstrap.Toast(toastHTML, option);
    toast.show();
    toastCounter++;

};
sock.onclose = function() {

    console.log('disconnected');

};

getEarthquakes(earthquakes);
