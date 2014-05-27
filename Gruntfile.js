/*jshint node:true*/
module.exports = function(grunt) {
    'use strict';
    // Project configuration
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        protractor: {
          options: {
            configFile: "referenceConf.js", // Default config file
            keepAlive: false, // If false, the grunt process stops when the test fails.
            noColor: false, // If true, protractor will not use colors in its output.
            args: {
              // Arguments passed to the command
            }
          },
          your_target: {
            options: {
              configFile: "e2e.conf.js", // Target-specific config file
              args: {} // Target-specific arguments
            }
          },
        },
        bower: {
        install: {
          options: {
            targetDir: './components',
            layout: 'byType',
            install: true,
            verbose: false,
            cleanTargetDir: true,
            cleanBowerDir: false,
            bowerOptions: {}
          }
        }
      },
        concat: {
            js: {
                src: [
                    // link scripts
                    'components/jquery/dist/jquery.js',
                    'components/angular/angular.js',
                    'components/bootstrap/dist/js/bootstrap.js',
                    // project scripts
                    'src/engine/app.js',
                    'src/engine/controllers/LifeAppController.js',
                    'src/engine/helpers/helper.objects.js'
                ],
                dest: 'build/js/scripts.js'
            },
            css: {
                src: [
                    'components/bootstrap/dist/css/bootstrap.css',
                    'components/fontawesome/css/font-awesome.css',
                    'src/css/style.css'
                ],
                dest: 'build/css/styles.css'
            }  
        },
        copy: {
             main: {
                files: [
                  {expand: true, flatten: true, src: ['components/bootstrap/dist/fonts/**', 'components/fontawesome/fonts/**'], dest: 'build/fonts/'},
                ]
              }
        },
        meta: {
            banner: '/*! <%= pkg.name %> Author: Anton Shashok, https://github.com/peedj, <%= grunt.template.today("yyyy") %> */'
        },
        uglify: {
            js: {
                src: [
                    '<banner>',
                    '<%= concat.js.dest %>'
                ],
                dest: 'build/js/scripts.min.js'
            }
        },
        cssmin: {
            css: {
                src: [
                    '<banner>',
                    '<%= concat.css.dest %>'
                ],
                dest: 'build/css/styles.min.css'
            }
        },
        watch: {
            concat: {
                files: ['components/**/*.js', "components/**/*.css", "engine/*.js", "engine/**/*.js", "css/style.js"],
                tasks: 'concat min'
            }
        },
        server: {
            port: 8000,
            base: '.'
        },
        jshint: {
            files: [
                'grunt.js',
                'js/*.js'
            ]
        },
        clean: {
            build: ["build"]
        }
    });
    
    grunt.loadNpmTasks('grunt-protractor-runner');
    grunt.loadNpmTasks('grunt-bower-task');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    // Project tasks
    grunt.registerTask('default', ['clean', 'bower', 'jshint', 'concat', 'copy', 'uglify', "cssmin", 
// Disabled, due to bug with current version of protractor for Mac OS        
//        'protractor' 
    ]);
};