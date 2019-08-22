       $(document).ready(function(){
  $('body').scrollspy({target: ".navbar", offset: 50});   

  $("header .navbar-default .navbar-nav li a").on('click', function(event) {

    if (this.hash !== "") {

      event.preventDefault();

      var hash = this.hash;

      $('html, body').animate({
        scrollTop: $(hash).offset().top - 50}, 800, function(){
        window.location.hash = hash;
      });
    } 
  });
});

     $(document).ready(function() {
              var owl1 = $("#owl-demo");
              $("#owl-demo").owlCarousel({
             
                  autoPlay: false, //Set AutoPlay to 3 seconds
             
                  items : 3,
                  itemsDesktop : [1199,4],
                  itemsDesktopSmall : [979,3],
                  itemsMobile : [769, 3] ,  
                  itemsMobile : [640, 1] ,
                  navigation:true
              });
        
            });
            
             $(document).ready(function() {
              var owl1 = $("#owl-demo2");
              $("#feature1").owlCarousel({
             
                  autoPlay: false, //Set AutoPlay to 3 seconds
             
                  items : 1,
                  itemsDesktop : [1199,1],
                  itemsDesktopSmall : [979,1],
                  itemsMobile : [769, 1] ,  
                  itemsMobile : [640, 1] ,
                  navigation:true
              });
        
             
            });
             $(document).ready(function() {
              $("#feature2").owlCarousel({
             
                  autoPlay: false, //Set AutoPlay to 3 seconds
             
                  items : 1,
                  itemsDesktop : [1199,1],
                  itemsDesktopSmall : [979,1],
                  itemsMobile : [769, 1] ,  
                  itemsMobile : [640, 1] ,
                  navigation:true
              });
        
             
            });
             $(document).ready(function() {
              $("#feature3").owlCarousel({
             
                  autoPlay: false, //Set AutoPlay to 3 seconds
             
                  items : 1,
                  itemsDesktop : [1199,1],
                  itemsDesktopSmall : [979,1],
                  itemsMobile : [769, 1] ,  
                  itemsMobile : [640, 1] ,
                  navigation:true
              });
        
             
            });
        	  
              $(document).ready(function() {
      $("#owl-client").owlCarousel({
     
          autoPlay: false, //Set AutoPlay to 3 seconds
     
          items : 6,
          itemsDesktop : [1199,4],
          itemsDesktopSmall : [979,3],
          itemsMobile : [769, 3] ,  
          itemsMobile : [640, 1] ,
		  navigation:true
      });

     
    });
     $(document).ready(function() {
      $("#blog").owlCarousel({
     
          autoPlay: false, //Set AutoPlay to 3 seconds
     
          items : 3,
          itemsDesktop : [1199,3],
          itemsDesktopSmall : [979,3],
          itemsMobile : [769, 2] ,  
          itemsMobile : [640, 1] ,
		  navigation:true
      });

     
    });

    
    