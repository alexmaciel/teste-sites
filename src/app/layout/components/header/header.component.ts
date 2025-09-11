import { Component, Input, OnDestroy, OnInit } from '@angular/core';
import { NavigationEnd, Router } from '@angular/router';
import { Subscription } from 'rxjs';

import { LocalizeRouterService } from '@gilsdav/ngx-translate-router';
import { LanguageService, TranslationService } from '../../../core';

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html'
})
export class HeaderComponent implements OnInit, OnDestroy {
  @Input() appHeaderDefaultContainer?: 'fixed' | 'fluid';
  @Input() appHeaderDefaultContainerClass = '';
  
  appHeaderDefaultFixedDesktop?: boolean = true;
  currentLang = '';

  private unsubscribe: Subscription[] = [];

  constructor(
    private router: Router,
    // Services
    private localize: LocalizeRouterService,
    private translation: TranslationService,
    // Public
    public languageService: LanguageService
  ) { 
    this.currentLang  = this.translation.getSelectedLanguage();
  }

  ngOnInit(): void {
    this.loadLanguages();
  }
     
  loadLanguages() {
    const sb = this.languageService.getLanguages().pipe(
    ).subscribe();
    this.unsubscribe.push(sb);   
  } 

  ngOnDestroy() {
    this.unsubscribe.forEach((sb) => sb.unsubscribe());
  }      

  switchLang(lang: string) {
    this.translation.setLanguage(lang); 
    const sb = this.router.events.subscribe((event: any) => {
      if (event instanceof NavigationEnd) {    
        this.currentLang  = this.translation.getSelectedLanguage();
      }
    });
    this.unsubscribe.push(sb);    
  }  
    
  calculateMenuItemCssClass(url: string): string {
    const path: any = this.localize.translateRoute(url)
    return checkIsActive(this.router.url, path) ? 'active' : '';
  }  
}

const getCurrentUrl = (pathname: string): string => {
  return pathname.split(/[?#]/)[0];
};

const checkIsActive = (pathname: string, url: string) => {
  const current = getCurrentUrl(pathname);
  if (!current || !url) {
    return false;
  }

  if (current === url) {
    return true;
  }

  if (current.indexOf(url) > -1) {
    return true;
  }

  return false;
};