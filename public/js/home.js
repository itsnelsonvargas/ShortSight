
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



   $('#customSlugInput').on('input', function () {
 
        const submitButton = $('#submitButton');
        let slug = $(this).val().trim();

        if (slug.length === 0) return;
        
        if (slug.includes(' ')) {
            $('#slug-status').text('Slug cannot contain spaces.').css('color', 'red');
            return;
        }

        const MIN_LENGTH = 6; // Minimum length for slug
        if(slug.length < MIN_LENGTH) {
            
            let neededChars = MIN_LENGTH - slug.length;
            $('#slug-status').text(`Slug must be at least ${MIN_LENGTH} characters long. ${neededChars} more character(s) needed.`).css('color', 'red');
            
           
           return;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
         
        $.ajax({
            url: checkSlugUrl, // URL to your slug checking endpoint. Declared from the Welcome.blade.php
            type: 'POST',
            data: { slug: slug },
            
            beforeSend: function () { 
               
                let spinnerLogo = '<i id="logo-check-slug" class="fa fa-spinner fa-spin" aria-hidden="true"></i>'
                $('#slug-status').html(spinnerLogo + ' Checking...');
            },
            success: function (response) {
                if (response.exists) { 
                    $('#slug-status').text('Slug already exists').css('color', 'red');

                } else {
                    let checkInCircle = '<i class="fa-solid fa-circle-check"></i>';
                    $('#slug-status').html( checkInCircle + ' Slug is available').css('color', 'green');
                }
            },
            error: function () {
                $('#slug-status').text('Error checking slug').css('color', 'orange');
            }
        });
    });




});
 


 