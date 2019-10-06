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
		}
	};

	require('load-grunt-tasks')(grunt);
	grunt.initConfig(configObject);
	grunt.registerTask('default', ['cssmin:plugin']);
};
