# Earthquake Now
## Background

The European Plate Observing System and the European-Mediterranean Seismological Centre provide scientific earthquake data through their [webservices](https://www.seismicportal.eu/). The goal of this project is to visualise and show that data on an interactive map. <br>
List of services used: 

| Name | Description | Direct url |
| --- | --- | --- | 
| Fdsn-event | web service for EMSC events | http://www.seismicportal.eu/fdsn-wsevent.html |
| Flinn-Engdahl Lookup | web service for FE region name | http://www.seismicportal.eu/feregions.html |
| Moment Tensors | web service for MT solutions |  http://www.seismicportal.eu/mtws/ |
| Felt reports | web service for Felt reports | http://www.seismicportal.eu/testimonies-ws/ |
| EventID | web service for event identifiers| http://www.seismicportal.eu/eventid |
| Rupture Models | web service for SRCMOD database | http://www.seismicportal.eu/srcmodws |
| (near) Real Time Notification | Service via websocket to get real time event notification | http://www.seismicportal.eu/realtime.html |

## Earthquake visualization
The map shows the location of earthquakes in the world using circles whose color and size corresponds to the magnitude. Clicking on an earthquake marker opens a small popup that includes basic information and a button to view details. Clicking anywhere else on the map, shows the name of the seismic region and the option to search for earthquakes in the area.
<br>
The map allows the selection of 4 different layers:
* Light
* Dark
* Satellite
* Optional tectonic plates layer

The sidebar on the left includes a list of earthquakes on the map, as well as a quick filter with pre-defined queries and an advanced filter, which allows custom paramaters. The project also includes a sock listener that displays a toast for earthquakes in real time.  

![Home](https://github.com/adnansmlatic/earthquake-now/blob/master/Screenshot_1.png)


## Earthquake details
This page contains in depth data about a specific earthquake. The ID of the earthquake is read from the GET id, which is then used for queries.  
The card on the left displays the core information about the earthquake and the map on the right shows the location of the epicenter, event origins, and the location of people that felt the earthquake and submitted their testimony. Below are 2 tables for event origins and moment tensors, the testimonies and photos tabs are static placeholders and don't work. The history tabs shows all significant (5+) earthquakes that occured in the area and the seismicity tab shows the historic activity of earthquakes in the area.
![Details](https://github.com/adnansmlatic/earthquake-now/blob/master/Screenshot_2.png)


## Website
The project website can be found at: [https://earthquakenow.cf/](https://earthquakenow.cf/) <br>
