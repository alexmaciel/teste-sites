import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root',
})
export class LayoutInitService {

    initProps() {
        // init base layout
        this.initLayoutSettings();
        this.initHeader();
    }

    private initLayoutSettings() {
        // clear body classes
        if (typeof document !== 'undefined') {
            const bodyClasses = document.body.classList.value.split(' ');
            bodyClasses.forEach((cssClass) => document.body.classList.remove(cssClass));
            // clear body attributes
            const bodyAttributes = document.body
            .getAttributeNames()
            .filter((t) => t.indexOf('data-') > -1);
            bodyAttributes.forEach((attr) => document.body.removeAttribute(attr));
            document.body.setAttribute('style', '');
            document.body.setAttribute('id', 'mv_app_body');
            document.body.setAttribute('data-mv-name', 'metabix');
            document.body.setAttribute('data-mv-app-header-fixed', 'false');
            document.body.setAttribute('data-mv-app-header-color', 'transparent');
            document.body.setAttribute('data-mv-app-sidebar-enabled', 'true');
            document.body.classList.add('app-default');      
        }  
    }

    private initHeader() {
		let timer = 0,
            scrollPosition = 0,
            height: number | any = 0;

        // clear body classes
        if (typeof document !== 'undefined') {        
            document.body.addEventListener('scroll', e => {
                scrollPosition = document.body.scrollTop;
                height = document.querySelector('.app-header')?.getBoundingClientRect().height;

                if(scrollPosition > height) {
                    document.body.setAttribute('data-mv-app-header-sticky', 'true');
                } else if (scrollPosition < height) {
                    document.body.removeAttribute('data-mv-app-header-sticky');
                }

                return;
            });    
        }    
    }
}