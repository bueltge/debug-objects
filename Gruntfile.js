module.exports = function(grunt) {

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
			all: {
				files: [{
					expand: true,
					cwd: 'js/',
					src: '*.dev.js',
					dest: 'js/',
					ext: '.js'
				}]
			}
		},
		cssmin: {
			all: {
				files: [{
					expand: true,
					cwd: 'css/',
					src: '*.dev.css',
					dest: 'css/',
					ext: '.css'
				}]
			}
		}
	});

	// Load the plugin that provides the "uglify" task.
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');

	// Default task(s).
	grunt.registerTask('default', ['uglify', 'cssmin']);

};
