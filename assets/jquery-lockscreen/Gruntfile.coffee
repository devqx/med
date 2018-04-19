'use strict'

module.exports = (grunt)->
  # project configuration
  grunt.initConfig
    # load package information
    pkg: grunt.file.readJSON 'package.json'

    meta:
      banner: "/* ===========================================================\n" +
        "# <%= pkg.title || pkg.name %> - v<%= pkg.version %>\n" +
        "# ==============================================================\n" +
        "# Copyright (c) <%= grunt.template.today(\"yyyy\") %> <%= pkg.author.name %>\n" +
        "# Licensed <%= _.pluck(pkg.licenses, \"type\").join(\", \") %>.\n" +
        "*/\n"

    coffeelint:
      options:
        indentation:
          value: 2
          level: "error"
        no_trailing_semicolons:
          level: "error"
        no_trailing_whitespace:
          level: "error"
        max_line_length:
          level: "ignore"
      default: ["Gruntfile.coffee", "src/**/*.coffee"]

    clean:
      default: "dist"
      test: "test"

    coffee:
      options:
        bare: true
      default:
        expand: true
        flatten: true
        cwd: "src/coffee"
        src: ["*.coffee"]
        dest: "dist"
        ext: ".js"
      test:
        expand: true
        flatten: true
        cwd: "src/spec"
        src: ["*.spec.coffee"]
        dest: "test"
        ext: ".spec.js"

    concat:
      options:
        banner: "<%= meta.banner %>"
        stripBanners: true
      dist:
        expand: true
        flatten: true
        cwd: "dist"
        src: ["*.js"]
        dest: "dist"
        ext: ".js"

    uglify:
      options:
        banner: "<%= meta.banner %>"
      dist:
        src: "dist/<%= pkg.name %>.js"
        dest: "dist/<%= pkg.name %>.min.js"

    # watching for changes
    watch:
      default:
        files: ["src/coffee/*.coffee"]
        tasks: ["build"]
      test:
        files: ["src/**/*.coffee"]
        tasks: ["test"]

    shell:
      options:
        stdout: true
        stderr: true
        failOnError: true
      publish:
        command: "npm publish"

    jasmine:
      options:
        keepRunner: false
        vendor: ["libs/jquery/jquery.js", "libs/jquery/jquery.cookie.js"]
        specs: "test/*.spec.js"
      src: "dist/<%= pkg.name %>.js"

    bump:
      options:
        files: ["package.json"]
        updateConfigs: ["pkg"]
        commit: true
        commitMessage: "Bump version to %VERSION%"
        commitFiles: ["-a"]
        createTag: true
        tagName: "v%VERSION%"
        tagMessage: "Version %VERSION%"
        push: true
        pushTo: "origin"
        gitDescribeOptions: "--tags --always --abbrev=1 --dirty=-d"

  # load plugins that provide the tasks defined in the config
  grunt.loadNpmTasks "grunt-bump"
  grunt.loadNpmTasks "grunt-coffeelint"
  grunt.loadNpmTasks "grunt-contrib-clean"
  grunt.loadNpmTasks "grunt-contrib-coffee"
  grunt.loadNpmTasks "grunt-contrib-concat"
  grunt.loadNpmTasks "grunt-contrib-jasmine"
  grunt.loadNpmTasks "grunt-contrib-uglify"
  grunt.loadNpmTasks "grunt-contrib-watch"
  grunt.loadNpmTasks "grunt-shell"

  # register tasks
  grunt.registerTask "default", ["build"]
  grunt.registerTask "build", ["clean", "coffeelint", "coffee", "concat", "uglify"]
  grunt.registerTask "test", ["build", "jasmine"]
  grunt.registerTask "release", "Release a new version, push it and publish it", (target)->
    target = "patch" unless target
    grunt.task.run "bump-only:#{target}", "test", "bump-commit", "shell:publish"
