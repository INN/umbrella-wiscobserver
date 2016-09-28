module.exports = function(grunt) {
    'use strict';

    // Force use of Unix newlines
    grunt.util.linefeed = '\n';

    // Find what the current theme's directory is, relative to the WordPress root
    var path = process.cwd().replace(/^[\s\S]+\/wp-content/, "\/wp-content");

    var CSS_LESS_FILES = {
        'css/child.css': 'less/child.less'
    };

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        less: {
            development: {
                options: {
                    paths: ['less', '../largo/less/inc'],
                    sourceMap: true,
                    outputSourceFiles: true,
                    sourceMapBasepath: path
                },
                files: CSS_LESS_FILES
            },
        },

        watch: {
            less: {
                files: [
                    'less/**/*.less'
                ],
                tasks: [
                    'less:development',
                    'cssmin'
                ]
            }
        },

        cssmin: {
            target: {
                options: {
                    report: 'gzip'
                },
                files: [{
                    expand: true,
                    cwd: 'css',
                    src: ['*.css', '!*.min.css'],
                    dest: 'css',
                    ext: '.min.css'
                }]
            }
        }

    });

    require('load-grunt-tasks')(grunt, { scope: 'devDependencies' });
}
