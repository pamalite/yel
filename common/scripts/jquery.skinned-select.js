jquery(document).ready(
  function() {
    jquery('.my-skinnable-select').each(
      function(i) {
        selectContainer = jquery(this);
        // Remove the class for non JS browsers
        selectContainer.removeClass('my-skinnable-select');
        // Add the class for JS Browers
        selectContainer.addClass('skinned-select');
        // Find the select box
        selectContainer.children().before('<div class="select-text">a</div>').each(
          function() {
            jquery(this).prev().text(this.options[0].innerHTML)
          }
        );
        // Store the parent object
        var parentTextObj = selectContainer.children().prev();
        // As we click on the options
        selectContainer.children().change(function() {
          // Set the value of the html
          parentTextObj.html(this.options[this.selectedIndex].innerHTML.replace('&nbsp;&nbsp;&nbsp;&nbsp;', ''));
					//jquery('div.bottom-line').prepend(jquery(this).prev('div.select-text').html());
					//alert(jquery(parentTextObj).attr('class'));
        })        
      }
    );
  }
);
