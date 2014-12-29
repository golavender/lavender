module.exports = function(grunt) {

  grunt.loadNpmTasks('grunt-exec');
  grunt.loadNpmTasks('grunt-contrib-watch');

  grunt.initConfig({
    exec: {
      lavender: {
        cmd: function(){

          var command     = "php ../../src/Lavender/lavender-cli.php views $view $destination;"
            , view        = "view=$(echo $file | sed -e 's/\\.lavender$//' | sed -e 's/^views\\///');"
            , destination = "destination=$(echo $file | sed -e 's/lavender$/html/' | sed -e 's/^views\\///');"
            , loop = "for file in `ls views/**/*.lavender && ls views/*.lavender`; do " + view + destination + command + " done"

          return loop;
        }
      }
    },
    watch: {
      scripts: {
        files: ['views/**/*.lavender'],
        tasks: ['exec:lavender'],
        options: {
          spawn: false,
        }
      }
    }
  });

  grunt.registerTask('default', ['watch']);
};
