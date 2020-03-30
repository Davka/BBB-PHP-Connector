# Simple PHP-BigBlueConnector

## Install

1. `run composer update`
2. Create .env.local file and add the api-informations

`BBB_API_ENDPOINT=<API_ENDPOINT>
 BBB_API_SECRET=<API_SHARED_SECRET>
`

To get the information run `bbb-conf --secret` on the BBB-Server


## Usage

There is currently only one function for reading out all active conferences

Run `php bin/console bbb:get-meetings`