import React from "react";

const About = () => {
  return (
    <section className="py-28 bg-white relative">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-wrap -mx-4">

          {/* Content Column */}
          <div className="w-full md:w-1/2 px-4 order-2 md:order-1 mb-12 md:mb-0">
            <div className="pl-6">
              <div className="mb-10">
                <span className="block text-red-600 text-lg font-medium mb-4">About Company</span>
                <h2 className="text-4xl font-semibold text-gray-900 leading-tight mb-4">
                  We are leader in <br />Industrial market Since 1992
                </h2>
              </div>
              <p className="text-gray-500 text-base leading-relaxed mb-8">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
                Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
              </p>
              <ul className="mb-10 space-y-3">
                <li className="pl-8 relative text-gray-800 text-base before:content-['\f058'] before:font-awesome before:absolute before:left-0 before:text-red-600 before:text-lg">
                  Lorem Ipsum is simply dummy text
                </li>
                <li className="pl-8 relative text-gray-800 text-base before:content-['\f058'] before:font-awesome before:absolute before:left-0 before:text-red-600 before:text-lg">
                  Consectetur adipisicing elit
                </li>
                <li className="pl-8 relative text-gray-800 text-base before:content-['\f058'] before:font-awesome before:absolute before:left-0 before:text-red-600 before:text-lg">
                  Sed do eiusmod tempor incididunt
                </li>
              </ul>
              <div>
                <a href="#" className="inline-block bg-red-600 text-white font-semibold text-base px-10 py-4 hover:bg-blue-900 transition duration-300">
                  Contact Us
                </a>
              </div>
            </div>
          </div>

          {/* Image Column */}
          <div className="w-full md:w-1/2 px-4 order-1 md:order-2">
            <div className="relative pl-24 pb-32">
              <figure className="relative z-10 mb-6">
                <a href="#" className="block">
                  <img src="https://i.ibb.co/QP6Nmpf/image-1-about.jpg" alt="About Us 1" className="shadow-lg" />
                </a>
              </figure>
              <figure className="absolute bottom-0 left-0 z-0">
                <a href="#" className="block">
                  <img src="https://i.ibb.co/JvN0NVB/image-2-about.jpg" alt="About Us 2" className="shadow-lg" />
                </a>
              </figure>

              {/* Decorative Background Circle */}
              <div
                className="absolute top-16 -left-20 w-[520px] h-[520px] bg-no-repeat bg-cover -z-10"
                style={{
                  backgroundImage: "url('https://i.ibb.co/fxJ1jtC/about-circle-1.png')"
                }}
              ></div>
            </div>
          </div>

        </div>
      </div>
    </section>
  );
};

export default About;
