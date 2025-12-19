
/*jshint esversion: 6 */
// TEMP -> Affichage en haut de l'écran de l'adresse de base pour la recherche de resto

var tabgps = [];
var radius = document.getElementById('pradius').textContent;
var userlat;
var userlon;
var markers = new L.MarkerClusterGroup({ maxClusterRadius: 60, disableClusteringAtZoom: 15 });
var results = [];



var request2 = new XMLHttpRequest();

request2.open("GET", "https://api-adresse.data.gouv.fr/search/?q=" + document.getElementById('paddr').textContent + "&limit=1");
request2.send();
request2.onreadystatechange = function () {
    if (this.readyState == XMLHttpRequest.DONE && this.status == 200) {
        var response = JSON.parse(this.responseText);
        // var tab = response;
        // Je récupère l'objet retourné par ma requete faites avec l'api de  et 
        //je l'ajoute dans mon select de manière dynamique
        document.getElementById('currentgps').textContent = response.features[0].geometry.coordinates[1] + "," + response.features[0].geometry.coordinates[0];
        userlat = response.features[0].geometry.coordinates[1];
        userlon = response.features[0].geometry.coordinates[0];
        getdata();

    };
}



// -> Affichage de la carte via leaflet centrée sur l'adresse de recherche
var L;
var map = L.map('map');
var osmUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
var osmAttrib = 'Map data © OpenStreetMap contributors';
var osm = new L.TileLayer(osmUrl,
    {
        attribution: osmAttrib
    });



function addResto() {

    map.setView([userlat, userlon], 11);
    map.addLayer(osm);

    L.marker([userlat, userlon],
        {
            title: "Adresse de départ"
        }).addTo(map);

    console.log('mapo');

    console.log('mapy');
    L.circle([userlat, userlon], radius * 1000, {
        'color': 'white',
        'fill': true,
        'fillColor': 'black',
        'fillOpacity': 0.1,
    }).addTo(map);


    for (let i = 0; i < tabgps.length; i++) {

        // Récupération de la distance en voiture entre le point de départ et les restos via une api -> précis mais extremement lent
        //  let distance = await getDistance(tabgps[i][0],tabgps[i][1],selectedCoord[1],selectedCoord[0]);
        // distance =distance.resourceSets[0].resources[0].results[0].travelDistance;


        // Récupération de la distance A VOL D'OISEAU entre le point de départ et les restos via un calcul mathematique -> peuprécis mais extremement rapide
        // Cette methode me permet de lancer cette fonction au chargement de la page
        let distance = distancex(tabgps[i][0], tabgps[i][1], userlat, userlon, "K");
        // console.log('Distance ' + distance);

        // Je test en dur les point se situant dans un rayon de 8km
        var test;


        switch (tabgps[i][3]) {

            case 'Boulangerie':

                test = L.icon({
                    iconUrl: 'images/icons/bakery.png',
                    iconSize: [38, 70], // size of the icon
                    className: ('marker' + tabgps[i][2]).replace(/\s+/g, '')
                });
                break;

            case 'Japonais':

                test = L.icon({
                    iconUrl: 'images/icons/sushi.png',
                    iconSize: [38, 70], // size of the icon
                    className: ('marker' + tabgps[i][2]).replace(/\s+/g, '')
                });
                break;

            case 'Divers':

                test = L.icon({
                    iconUrl: 'images/icons/divers.png',
                    iconSize: [38, 70], // size of the icon
                    className: ('marker' + tabgps[i][2]).replace(/\s+/g, '')
                });
                break;

            case 'Pizzeria':
                test = L.icon({
                    iconUrl: 'images/icons/pizza.png',
                    iconSize: [38, 70], // size of the icon
                    className: ('marker' + tabgps[i][2]).replace(/\s+/g, '')
                });
                break;

            default:
                test = L.icon({
                    iconUrl: 'images/icons/pizza.png',
                    iconSize: [38, 70], // size of the icon
                    className: ('marker' + tabgps[i][2]).replace(/\s+/g, '')
                });
        }

        if (distance <= radius) {

            var a = L.marker([tabgps[i][0], tabgps[i][1]],
                {

                    title: tabgps[i][2],
                    icon: test,
                    className: 'toto'
                }).bindPopup("<b>" + tabgps[i][2] + "</b><br>" + tabgps[i][3] + "<br>  <a href='https://www.google.com/maps/dir/?api=1&amp;destination=" + tabgps[i][4] + "," + tabgps[i][5] + " " + tabgps[i][6] + "&amp;origin= class='aiti'><img src='images/icons/itinerary.png' style='height: 50px;'><figcaption>Itinéraire</figcaption> </a>")
                .openPopup();



            markers.addLayer(a);

            a.on('mouseover ', function (e) {
                document.getElementById('section' + tabgps[i][2]).style.backgroundColor = 'rgba(192, 226, 164, 0.562)';
            });

            a.on('mouseout ', function (e) {
                document.getElementById('section' + tabgps[i][2]).style.backgroundColor = 'rgb(243,242,242)';
            });

            markers.on('clustermouseover ', function (e) {
                console.log('test de click sur marker');
            });

            var oResto = new Object();
            oResto.lat = tabgps[i][0];
            oResto.long = tabgps[i][1];
            oResto.nom = tabgps[i][2];
            oResto.type = tabgps[i][3];
            oResto.adresse = tabgps[i][4];
            oResto.codePostal = tabgps[i][5];
            oResto.ville = tabgps[i][6];
            oResto.desciptif = tabgps[i][7];
            oResto.distance = distance.toFixed(2);

            results.push(oResto);
        }
    }






    

    map.addLayer(markers);

    //console.log("fin de test");
}





function getdata() {

    var type = document.getElementById('pType').textContent;
    var start = document.getElementById('start').textContent;
    var nb = document.getElementById('nb').textContent;
    var tri = document.getElementById('tri').textContent;
    var mOptions = document.getElementById('mOptions').textContent;
    $.ajax(
        {
            type: 'GET',
            url: 'getdata.php?type=' + type + '&start=' + start + '&nb=' + nb + '&lat=' + userlat + '&lon=' + userlon + '&radius=' + radius + '&tri=' + tri + '&mOptions=' + mOptions,

            success: function (data) {
                var data2 = JSON.parse(data);



                //console.log(sessionStorage.getItem("user"));

                for (let i = 0; i < data2.length; i++) {

                    //console.log("coordonnées de " + data2[i].nom + " - (" + data2[i].gps + ")");

                    //la valeur de data[i].gps est une string contenant 'lat,long'
                    //Je dois donc splitter la chaine afin d'avoir un tableau de 2 éléments
                    // le premier sera la latitude et le second, la longitude

                    var gps = data2[i].gps;
                    var gpsSplit = gps.split(",");
                    tabgps[i] = [gpsSplit[0], gpsSplit[1], data2[i].nom, data2[i].type, data2[i].adresse, data2[i].codePostal, data2[i].ville, data2[i].descriptif];
                }
            },
            complete: function () {

                addResto();
            }
        });
}



function distancex(lat1, lon1, lat2, lon2, unit) {
    if ((lat1 == lat2) && (lon1 == lon2)) {
        return 0;
    }
    else {
        var radlat1 = Math.PI * lat1 / 180;
        var radlat2 = Math.PI * lat2 / 180;
        var theta = lon1 - lon2;
        var radtheta = Math.PI * theta / 180;
        var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
        if (dist > 1) {
            dist = 1;
        }
        dist = Math.acos(dist);
        dist = dist * 180 / Math.PI;
        dist = dist * 60 * 1.1515;
        if (unit == "K") { dist = dist * 1.609344; }
        if (unit == "N") { dist = dist * 0.8684; }
        return dist;
    }
}



var userSelection = document.getElementsByClassName('sqlIm');

for (let i = 0; i < userSelection.length; i++) {

    userSelection[i].addEventListener("click", function () {

        let optionsTab = [];
        var data2;


        $.ajax(
            {
                type: 'POST',
                url: 'getdata.php',
                data: 'nom=' + this.id,

                success: function (data) {


                    data2 = JSON.parse(data);
                    console.log(data2);
                    var gps = data2.gps.split(",");

                    document.getElementById('restotitle').textContent = "Restaurant " + data2.Type + "-" + data2.Nom;

                    document.getElementById('adresseresto').textContent = data2.adresse + " - " + data2.codePostal + " " + data2.ville;

                    document.getElementById('descriptif').innerHTML = data2.descriptif;

                    document.getElementById('restoPic').setAttribute("src", "images/vendeur/" + data2.main);
                    document.getElementById('restoSlide1').src = "images/vendeur/" + data2.slide1;
                    document.getElementById('restoSlide2').src = "images/vendeur/" + data2.slide2;
                    document.getElementById('restoSlide3').src = "images/vendeur/" + data2.slide3;

                    document.getElementById('gmap_canvas').src = "https://maps.google.com/maps?q=" + data2.adresse + " " + data2.codePostal + " " + data2.ville + "&t=&z=15&ie=UTF8&iwloc=&output=embed";
                    document.getElementById('restoPhone').textContent = data2.phone;
                    document.getElementById('restoWeb').textContent = data2.web;


                    map.setView([gps[0], gps[1]], 19);
                },
                complete: function () {

                    $.ajax(
                        {
                            type: 'POST',
                            url: 'getdata.php',
                            data: 'options=1',


                            success: function (options) {

                                //dans getdata.php, je récupère toutes les options (wifi,parking etc....) 
                                // je colle le resultat dans un tableau -> optionsTab

                                optionsTab = options.replaceAll("\"", "").replaceAll("[", "").replaceAll("]", "").replaceAll("\n", "").replaceAll(" ", "");
                                optionsTab = optionsTab.split(",");


                            },
                            complete: function () {



                                // Les options étant dans un tableau , je compare les infos du resto sur lequelle j'ai cliqué pour voir quelles options sont à 1 dans la db
                                // pour info, pour accéder aux propriétés d'un objet lorsque la propriété est une string , nous utilisons les bracket.
                                //obect.toto et  object.["toto"] afficheront le même résultat   

                                let strOptions = "";
                                for (let i = 0; i < optionsTab.length; i++) {


                                    if (data2[optionsTab[i]] == 1) {

                                        strOptions += '<div class="col-md-4"><label class="custom-checkbox"><span class="ti-check-box"></span><span class="custom-control-description">' + optionsTab[i] + '</span>                   </label> </div>';

                                    }

                                    document.getElementById('displayOptions').innerHTML = strOptions;
                                }
                            }
                        });
                }
            });
    })
}


