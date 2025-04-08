
alert(1);
//to be added at the footers (before the closing body tag)
//this function copies the shortened URL to the clipboard


function copyToClipboard(url) {
   const el = document.createElement('textarea');
   el.value = url;
   document.body.appendChild(el);
   el.select();
   document.execCommand('copy');
   document.body.removeChild(el);
   alert('Copied to clipboard!');
} 


$(document).ready(function () {
   function isValidURL(url) {
       var pattern = /^(https?:\/\/)?(www\.)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?(\/[^\s]*)?$/;
       return pattern.test(url);
   }

   $("#url").on("input", function () {
       if (isValidURL($(this).val().trim())) {
           $(".check-icon").remove(); // Removes the checkmark permanently
       } else {
           $(".check-icon").fadeOut();
       }
   });
});
 