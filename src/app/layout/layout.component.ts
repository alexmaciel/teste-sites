import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { LayoutInitService } from './services/layout-init.service';
import { SplashScreenService } from './components/splash-screen/splash-screen.service';

@Component({
  selector: 'app-layout',
  templateUrl: './layout.component.html',
  styleUrl: './layout.component.scss'
})
export class LayoutComponent {
  // Public variables
  // page
  pageContainerCSSClasses!: string;
  // header
  appHeaderDefaultClass = '';  
  appHeaderDefaultContainer: 'fixed' | 'fluid' = 'fluid';
  appHeaderDefaultContainerClass = '';  
  headerContainerCssClass = '';
  // content
  appContentContainer?: 'fixed' | 'fluid' = 'fluid';
  appContentContainerClass!: string;
  contentCSSClasses!: string;
  contentContainerCSSClass!: string;  
  // footer
  appFooterContainer?: 'fixed' | 'fluid' = 'fluid';
  appFooterContainerCSSClass = '';


  constructor(
    private router: Router,
    private initService: LayoutInitService,
  ) {

    this.initService.initProps();
    // define layout type and load layout
    this.router.events.subscribe((event) => {
      if (event.constructor.name.endsWith('End')) {
        this.initService.initProps();
      }
    });    
  }  
}
