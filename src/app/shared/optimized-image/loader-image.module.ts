import { NgModule } from '@angular/core';
import { CommonModule, NgOptimizedImage, IMAGE_LOADER, ImageLoaderConfig, IMAGE_CONFIG } from '@angular/common';

// The origin of the CDN we are going to use to pull images from.
// const base = 'https://images.unsplash.com';

const customLoader = (config: ImageLoaderConfig) => {
  // Join the value that the user put in the `ngSrc` attribute and the CDN base into a single URL.
  const url = `${config.src}`; //new URL(config.src);
  const searchParams: any = [];

  if (config.width) {
    url + searchParams.push('w', config.width.toString());
  }

  if (config.loaderParams?.['compression']) {
    url + searchParams.push('q', config.loaderParams['compression']);
  }

  return url.toString();

  /*
  let url = `${config.src}`;
  let queryParams = [];
  if (config.width) {
    queryParams.push(`?w=${config.width}`);
  }

  return url + queryParams.join('&');
  */
};

@NgModule({
  imports: [
    CommonModule,
    NgOptimizedImage
  ],
  exports: [
    NgOptimizedImage
  ],
  // Configure the loader using the `IMAGE_LOADER` token.
  providers: [
    {
      provide: IMAGE_LOADER,
      useValue: customLoader
    },
    {
      provide: IMAGE_CONFIG,
      useValue: {
        breakpoints: [380, 640, 1200, 1920, 2048, 3840],
        placeholderResolution: 25
      }
    },    
  ],   
})
export class NgxOptimizedImageModule { }
