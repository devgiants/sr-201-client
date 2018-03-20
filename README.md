# SR-201 web relay client app
## Presentation

This is a simple app to control and configure SR-201 web relays. You can find them for buying on multiple places, just [search for it](https://www.google.fr/search?q=SR-201+web+relay)

## Installation

```
# Get the application
wget https://devgiants.github.io/sr-201-client/downloads/sr-201-client-1.0.0.phar

# Move it in command folder
mv sr-201-client-1.0.0.phar /usr/bin/sr-201

# Make it executable
chmod u+x /usr/bin/sr-201
```

## Usage

On first power on, relay will have following parameters :
- IP : 192.168.1.100 (statically set)
- Subnet mask : 255.255.255.0
- Gateway : 192.168.1.1 

### Configuration

Use following command for changing network parameters.

```
sr-201 config [--current-ip=192.168.1.100] --new-ip=X.X.X.X [--subnet=255.255.255.0 --gateway=192.168.1.1]
```
 
_Note : the cloud setup is missing on purpose, I assume that you will use this command from a more evolved device (RPI, PC...) on the same network. AFAIC, the toggling task are delegated to my [OpenHAB](https://www.openhab.org/) device._

### Changing relays states

```
sr-201 switch --ip=X.X.X.X --channel=X --state=1
```

## Theory

According to documentation, web relay server listens :
- TCP port 6722 : control relay (toggle states)
- UDP port 6723 : control relay (toggle states)
- TCP port 5111 : relay configuration