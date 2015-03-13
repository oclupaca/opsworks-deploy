require 'net/http'
require 'json'

node[:deploy].each do |application, deploy|
	puts "application:"
	puts application.inspect
	puts "deploy:"
	puts deploy.inspect
	puts "domains"
	my_domains = []
	deploy.domains.each do |domain|
		puts domain
		if domain != application
			my_domains.push(domain)
		end
	end
	my_domain_csv = my_domains.join(",\n")
	puts "my_domain_csv:"
	puts my_domain_csv

	payload = {
		'channel' => '@paul',
		'username' => 'OpsWorks',
		'text' => '*' + application + '* has been deployed' + "\n" + my_domain_csv,
		'icon_emoji' => ':heavy_check_mark:'
	}

	postData = Net::HTTP.post_form(URI.parse('https://hooks.slack.com/services/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'), {
		"payload" => payload.to_json
		})

	puts postData
end