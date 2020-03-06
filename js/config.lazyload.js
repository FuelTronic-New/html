// lazyload config

angular.module('app')
    /**
   * jQuery plugin config use ui-jq directive , config the js and css files that required
   * key: function name of the jQuery plugin
   * value: array of the css js file located
   */
  .constant('JQ_CONFIG', {
      angularScroll:['vendor/angular-scroll/angular-scroll.js'],
      easyPieChart:   [   'vendor/jquery/jquery.easy-pie-chart/dist/jquery.easypiechart.fill.js'],
      sparkline:      [   'vendor/jquery/jquery.sparkline/dist/jquery.sparkline.retina.js'],
      plot:           [   'vendor/jquery/flot/jquery.flot.js',
                          'vendor/jquery/flot/jquery.flot.pie.js',
                          'vendor/jquery/flot/jquery.flot.resize.js',
                          'vendor/jquery/flot.tooltip/js/jquery.flot.tooltip.min.js',
                          'vendor/jquery/flot.orderbars/js/jquery.flot.orderBars.js',
                          'vendor/jquery/flot-spline/js/jquery.flot.spline.min.js'],
      moment:         [   'vendor/jquery/moment/moment.js'],
      screenfull:     [   'vendor/jquery/screenfull/dist/screenfull.min.js'],
      slimScroll:     [   'vendor/jquery/slimscroll/jquery.slimscroll.min.js'],
      sortable:       [   'vendor/jquery/html5sortable/jquery.sortable.js'],
      nestable:       [   'vendor/jquery/nestable/jquery.nestable.js',
                          'vendor/jquery/nestable/jquery.nestable.css'],
      filestyle:      [   'vendor/jquery/bootstrap-filestyle/src/bootstrap-filestyle.js'],
      slider:         [   'vendor/jquery/bootstrap-slider/bootstrap-slider.js',
                          'vendor/jquery/bootstrap-slider/bootstrap-slider.css'],
      chosen:         [   'vendor/jquery/chosen/chosen.jquery.min.js',
                          'vendor/jquery/chosen/bootstrap-chosen.css'],
      TouchSpin:      [   'vendor/jquery/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js',
                          'vendor/jquery/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css'],
      wysiwyg:        [   'vendor/jquery/bootstrap-wysiwyg/bootstrap-wysiwyg.js',
                          'vendor/jquery/bootstrap-wysiwyg/external/jquery.hotkeys.js'],
      dataTable:      [   'vendor/jquery/datatables/media/js/jquery.dataTables.min.js',
                          'vendor/jquery/plugins/integration/bootstrap/3/dataTables.bootstrap.js',
                          'vendor/jquery/plugins/integration/bootstrap/3/dataTables.bootstrap.css'],
      vectorMap:      [   'vendor/jquery/bower-jvectormap/jquery-jvectormap-1.2.2.min.js',
                          'vendor/jquery/bower-jvectormap/jquery-jvectormap-world-mill-en.js',
                          'vendor/jquery/bower-jvectormap/jquery-jvectormap-us-aea-en.js',
                          'vendor/jquery/bower-jvectormap/jquery-jvectormap.css'],
      footable:       [   'vendor/jquery/footable/v3/js/footable.min.js',
                          'vendor/jquery/footable/v3/css/footable.bootstrap.min.css'],
      fullcalendar:   [   'vendor/jquery/moment/moment.js',
                          'vendor/jquery/fullcalendar/dist/fullcalendar.min.js',
                          'vendor/jquery/fullcalendar/dist/fullcalendar.css',
                          'vendor/jquery/fullcalendar/dist/fullcalendar.theme.css'],
      daterangepicker:[   'vendor/jquery/moment/moment.js',
                          'vendor/jquery/bootstrap-daterangepicker/daterangepicker.js',
                          'vendor/jquery/bootstrap-daterangepicker/daterangepicker-bs3.css'],
      tagsinput:      [   'vendor/jquery/bootstrap-tagsinput/dist/bootstrap-tagsinput.js',
                          'vendor/jquery/bootstrap-tagsinput/dist/bootstrap-tagsinput.css']
                      
    }
  )
  .constant('MODULE_CONFIG', [
      {
          name: 'ngGrid',
          files: [
              'vendor/angular/ng-grid/build/ng-grid.min.js',
              'vendor/angular/ng-grid/ng-grid.min.css',
              'vendor/angular/ng-grid/ng-grid.bootstrap.css'
          ]
      },
      {
          name: 'ui.grid',
          files: [
              'vendor/angular/angular-ui-grid/ui-grid.min.js',
              'vendor/angular/angular-ui-grid/ui-grid.min.css',
              'vendor/angular/angular-ui-grid/ui-grid.bootstrap.css'
          ]
      },
      {
          name: 'ui.select',
          files: [
              'vendor/angular/angular-ui-select/dist/select.min.js',
              'vendor/angular/angular-ui-select/dist/select.min.css'
          ]
      },
      {
          name:'angularFileUpload',
          files: [
            'vendor/angular/angular-file-upload/angular-file-upload.js'
          ]
      },
      {
          name:'ui.calendar',
          files: ['vendor/angular/angular-ui-calendar/src/calendar.js']
      },
      {
          name: 'ngImgCrop',
          files: [
              'vendor/angular/ngImgCrop/compile/minified/ng-img-crop.js',
              'vendor/angular/ngImgCrop/compile/minified/ng-img-crop.css'
          ]
      },
      {
          name: 'angularBootstrapNavTree',
          files: [
              'vendor/angular/angular-bootstrap-nav-tree/dist/abn_tree_directive.js',
              'vendor/angular/angular-bootstrap-nav-tree/dist/abn_tree.css'
          ]
      },
      {
          name: 'toaster',
          files: [
              'vendor/angular/angularjs-toaster/toaster.js',
              'vendor/angular/angularjs-toaster/toaster.css'
          ]
      },
      {
          name: 'textAngular',
          files: [
              'vendor/angular/textAngular/dist/textAngular-sanitize.min.js',
              'vendor/angular/textAngular/dist/textAngular.min.js'
          ]
      },
      {
          name: 'vr.directives.slider',
          files: [
              'vendor/angular/venturocket-angular-slider/build/angular-slider.min.js',
              'vendor/angular/venturocket-angular-slider/build/angular-slider.css'
          ]
      },
      {
          name: 'com.2fdevs.videogular',
          files: [
              'vendor/angular/videogular/videogular.min.js'
          ]
      },
      {
          name: 'com.2fdevs.videogular.plugins.controls',
          files: [
              'vendor/angular/videogular-controls/controls.min.js'
          ]
      },
      {
          name: 'com.2fdevs.videogular.plugins.buffering',
          files: [
              'vendor/angular/videogular-buffering/buffering.min.js'
          ]
      },
      {
          name: 'com.2fdevs.videogular.plugins.overlayplay',
          files: [
              'vendor/angular/videogular-overlay-play/overlay-play.min.js'
          ]
      },
      {
          name: 'com.2fdevs.videogular.plugins.poster',
          files: [
              'vendor/angular/videogular-poster/poster.min.js'
          ]
      },
      {
          name: 'com.2fdevs.videogular.plugins.imaads',
          files: [
              'vendor/angular/videogular-ima-ads/ima-ads.min.js'
          ]
      },
      {
          name: 'xeditable',
          files: [
              'vendor/angular/angular-xeditable/dist/js/xeditable.min.js',
              'vendor/angular/angular-xeditable/dist/css/xeditable.css'
          ]
      },
      {
          name: 'smart-table',
          files: [
              'vendor/angular/angular-smart-table/dist/smart-table.min.js'
          ]
      },
      {
          name: 'angular-skycons',
          files: [
              'vendor/angular/angular-skycons/angular-skycons.js'
          ]
      }
    ]
  )
  // oclazyload config
  .config(['$ocLazyLoadProvider', 'MODULE_CONFIG', function($ocLazyLoadProvider, MODULE_CONFIG) {
      // We configure ocLazyLoad to use the lib script.js as the async loader
      $ocLazyLoadProvider.config({
          debug:  false,
          events: true,
          modules: MODULE_CONFIG
      });
  }])
;
