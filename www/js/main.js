


var map = L.map('map', {
    zoomControl: false
}).setView([50.086, 14.408], 14);



var osm = L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: 'OpenStreetMap.org',
    opacity: 0.6
}).addTo(map)


var lines = L.multiPolyline([],{
    weight: 1,
    color: '#000',
    opacity: 0.8
}).addTo(map);

//var userLines = [];
showLinesFor = function(uid){
    lines.setLatLngs([userLines[uid]]);
    map.fitBounds(lines.getBounds());
}

var marek = L.marker([0,0]).addTo(map);



var mq = L.tileLayer('http://otile1.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: 'Tiles Courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a> <img src="http://developer.mapquest.com/content/osm/mq_logo.png">'
}).addTo(map)


var baseLayers = {
    "Mapquest": mq,
    "OpenStreetMap": osm,
};

var overlays = {};

L.control.layers(baseLayers, overlays).addTo(map);

$(function(){

});
