import { Component, Inject, NgZone, PLATFORM_ID, OnInit, OnDestroy } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';

// amCharts imports
import * as am5 from "@amcharts/amcharts5";
import * as am5map from "@amcharts/amcharts5/map";
import am5themes_Animated from "@amcharts/amcharts5/themes/Animated";

// Geodata (mapa simplificado do mundo)
import am5geodata_worldLow from "@amcharts/amcharts5-geodata/worldLow";

@Component({
  selector: 'app-location',
  templateUrl: './location.component.html'
})
export class LocationComponent implements OnInit, OnDestroy {
  private root!: am5.Root;

  countries = [
    { id: "BR", polygonSettings: { fill: am5.color(0xE66B23) } }, 
    { id: "AR", polygonSettings: { fill: am5.color(0xE66B23) } }, // Argentina
    { id: "PE", polygonSettings: { fill: am5.color(0xE66B23) } }, // Peru
    { id: "IN", polygonSettings: { fill: am5.color(0xE66B23) } }, // Índia
    { id: "ID", polygonSettings: { fill: am5.color(0xE66B23) } }
  ];

  constructor(@Inject(PLATFORM_ID) private platformId: object, private zone: NgZone) {}

  // Run the function only in the browser
  browserOnly(f: () => void) {
    if (isPlatformBrowser(this.platformId)) {
      this.zone.runOutsideAngular(() => {
        f();
      });
    }
  }

 ngOnInit() {
    const series = Array.from({length: this.countries.length}, 
      (value, key) =>  
      this.countries[key]
    );   
    // Chart code goes in here
    this.browserOnly(() => {
      const root = am5.Root.new("chartdiv");
      
      this.root = root;

      root.setThemes([am5themes_Animated.new(root)]);

      // Criar o mapa
      const chart = root.container.children.push(
        am5map.MapChart.new(root, {
          panX: "none",
          panY: "none",
          wheelX: "none",
          wheelY: "none",
          pinchZoom: false,
          projection: am5map.geoMercator()
        })
      );

      // Criar a série de polígonos (países)
      const polygonSeries = chart.series.push(
        am5map.MapPolygonSeries.new(root, {
          geoJSON: am5geodata_worldLow,
          exclude: ["AQ"], // Exclui a Antártida
        })
      );

      polygonSeries.mapPolygons.template.setAll({
        tooltipText: "{name}",
        interactive: true,
        fill: am5.color(0xAAAAAA),       
        templateField: "polygonSettings",
      });

      polygonSeries.mapPolygons.template.states.create("hover", {
        fill: am5.color(0xE66B23)
      });

      // Definir países em destaque (laranja)
      polygonSeries.data.setAll(series);      
    });
  }

  ngOnDestroy() {
    if (this.root) {
      this.root.dispose();
    }
  }    
}
