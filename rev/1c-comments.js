function rates(index){

document.getElementById('cnote').value=(index*2);

}



var comments = {
  // (A) AJAX HELPER FUNCTION
  ajax : function (data, after) {
    // (A1) DATA
    var fdata = new FormData();
    for (let k in data) { fdata.append(k, data[k]); }
 

    // (A2) AJAX REQUEST
    var xhr = new XMLHttpRequest();
    xhr.open('POST', "rev/ajax-comments.php");
    xhr.onload = after;
    xhr.send(fdata);
  },
  
  // (B) LOAD COMMENTS
  load : function () {
    comments.ajax(
      // (B1) DATA TO SEND
      { req: "show",nom : document.getElementById("cresto").value, }, 

      // (B2) PUT SERVER RESPONSE INTO COMMENTS WRAPPER
      function(){
        document.getElementById("cwrap").innerHTML = this.response;
      }
    );
  },
  
  // (C) ADD COMMENT
  add: function () {
    comments.ajax(
      // (C1) DATA TO SEND
      {
        req : "add",
        name : document.getElementById("cname").value,
        nom : document.getElementById("cresto").value,
        cnote : document.getElementById("cnote").value,
        msg : document.getElementById("cmsg").value
      },
      
      // (C2) RELOAD COMMENTS AFTER ADD
      function(e){
        if (this.response == "OK") {
          document.getElementById("cadd").reset();
          comments.load();
          //alert(this.response);
        } else {
          alert(this.response);
        }
      }
    );
    return false;
  }
};
window.addEventListener("DOMContentLoaded", comments.load);