/*jshint esversion: 6 */
//Je récupère les différents type de resto dans la base de données
// puis je les ajoute au selecte correspondant aux spécialités

function toto(){

    $('.selectpicker').selectpicker('refresh');
 }
let rType=[];


//Au chargement de la page, e récupère les différents type de resto dans la base de données
// puis je les ajoute au selecte correspondant aux spécialités
window.onload = function ()
{
    
getType();
}



//requête AJAX pour récupérer les types dans la DB
// et j'affecte les données qui me sont remontées à la variable rType

function getType(){

$.ajax(
    {
        type: 'GET',
        url: 'gettype.php',
    
        success: function (data)
        {
            var data2 = JSON.parse(data);
            
            for (let i = 0; i < data2.length; i++)
            {
    
                 rType[i]= data2[i].type;
            }aadToSelect();
        }
    });
      
    }



// je recupère les type de resto contenu dans la var rType et je les affecte au select correspondant

function aadToSelect(){

   let fSelect=document.getElementById('typelist');

    for(let i=0;i<rType.length;i++){
    
 
let tOption=document.createElement('option');
tOption.value=rType[i];
tOption.text=rType[i];
fSelect.appendChild(tOption);
    }
    $('.selectpicker').selectpicker('refresh');
         
    }