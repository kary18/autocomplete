
var _xmlHttp = null; //l'objet xmlHttpRequest utilisé pour contacter le serveur
var _dataList = null;
var _inputField=null;

var _oldInputFieldValue=""; // valeur précédente du champ texte
var _currentInputFieldValue=""; // valeur actuelle du champ texte


window.onload = function(){
    _dataList = document.getElementById('loc-datalist');
    _inputField=document.getElementById('loc-input');
    mainLoop();
}

// tourne en permanence pour suggérer suite à un changement du champ texte
function mainLoop(){

  _currentInputFieldValue = _inputField.value;
  if(_oldInputFieldValue!=_currentInputFieldValue){
      var valeur=encodeURIComponent(_currentInputFieldValue);
      callSuggestions(valeur) // appel distant

      _inputField.blur();
      _inputField.focus();
  }
  _oldInputFieldValue=_currentInputFieldValue;
  setTimeout("mainLoop()",200); // la fonction se redéclenchera dans 200 ms
  return true
}


function callSuggestions(valeur){
  if(_xmlHttp&&_xmlHttp.readyState!=0){
    _xmlHttp.abort()
  }
  _xmlHttp= new XMLHttpRequest();

  if(_xmlHttp){
    //appel à l'url distante
    console.log('appel='+valeur);
    _xmlHttp.open("GET","loc-json.php?search="+valeur,true);
    _xmlHttp.onreadystatechange=function() {
      if(_xmlHttp.readyState===4&&_xmlHttp.status === 200) {

          var jsonOptions = JSON.parse(_xmlHttp.responseText);
          metsEnPlace(jsonOptions);
      }
    };
    // envoi de la requête
    _xmlHttp.send(null)
  }
}

function metsEnPlace(jsonOptions) {

  while(_dataList.childNodes.length>0) {
    _dataList.removeChild(_dataList.childNodes[0]);
  }

  // Loop over the JSON array.
  jsonOptions.forEach(function (item) {
      // Create a new <option> element.
      var option = document.createElement('option');
      // Set the value using the item in the JSON array.
      console.log(item);
      option.value = item;
      // Add the <option> element to the <datalist>.
      _dataList.appendChild(option);
  });

}
