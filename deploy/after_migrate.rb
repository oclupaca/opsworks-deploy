[release_path].each do |path|
    log "before_migrate hook [#{path}]" do
        level :debug
    end

    # execute "download composer" do
    #     user "root"
    #     cwd path
    #     command "wget https://getcomposer.org/installer -O composer.phar"
    # end
    # execute "install composer" do
    #     user "root"
    #     cwd path
    #     command "php composer.phar install"
    # end
    execute "composer install" do
        user "root"
        cwd path
        command "sudo php composer.phar install"
        # only execute for composer projects
        # only_if "test -f \"#{path}/composer.json\""
    end

end