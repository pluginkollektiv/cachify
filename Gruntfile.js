module.exports = function (grunt) {
	var configObject = {
		config: {
			root: './',
			scripts: {
				src: './js/'
			},
			styles: {
				src: './css/',
			}
		},
		cssmin: {
			options: {
				noAdvanced: true
			},
			plugin: {
				expand: true,
				cwd: '<%= config.styles.src %>',
				dest: '<%= config.styles.src %>',
				ext: '.min.css',
				src: ['*.css', '!*.min.css']
			}
		},
		uglify: {
			plugin: {
				expand: true,
				ext: '.min.js',
				cwd: '<%= config.scripts.src %>',
				dest: '<%= config.scripts.src %>',
				src: ['*.js', '!*.min.js', '!prism.js']
			}
		}
	};
	
	require('load-grunt-tasks')(grunt);
	grunt.initConfig(configObject);
	grunt.registerTask('default', ['cssmin:plugin','uglify:plugin']);
};