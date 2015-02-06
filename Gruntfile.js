'use strict';

module.exports = function (grunt) {

    grunt.initConfig({
        watch: {
            files: ['src/Generator/**/*'],
            tasks: ['shell:generator'],
        },
        shell: {
            options: {
                stdout: true,
            },
            generator: {
                command: './console crud:generator --tables=banks'
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-shell');

    grunt.registerTask('default', ['watch']);
};
