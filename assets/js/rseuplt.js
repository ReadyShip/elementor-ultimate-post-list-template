// Elementor JS Hooks
(function($){
  var postListHandler = function ($scope, $) {
    /* Masonry */
    var $postsWrapper = $scope.find('.rseuplt-post-list-wrapper');
    var masonryFlag = $postsWrapper.data('rseuplt-masonry');
    if (masonryFlag) {
      $postsWrapper.masonry({
        itemSelector: '.rseuplt-post-list-container',
        percentPosition: true
      });
      $postsWrapper.imagesLoaded().progress( function() {
        $postsWrapper.masonry('layout');
      })
    }

    /* postlink */
    $postsWrapper.find('.rseuplt-post-list-container > *').click(function (e) {
      var $parentElement = $(this).closest('.rseuplt-post-list-container')
      if(
        !elementorFrontend.isEditMode()
        && $parentElement.hasClass('rseuplt-post-list-linking')
        && $parentElement.data('href') !== '#post_permalink'
        // Avoid breaking default "A" tag link behavior
        && !(e.target.tagName === 'A' || $(e.target).closest('a').length > 0)
      ) {
        window.location.href = $parentElement.data('href')
      }
    })
  }
  $(window).on('elementor/frontend/init', function () {
    elementorFrontend.hooks.addAction(
      'frontend/element_ready/readyship-elementor-ultimate-post-list-template.default',
      postListHandler
    );
  });
})(jQuery)


