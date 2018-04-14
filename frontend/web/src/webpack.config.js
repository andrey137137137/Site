const path = require("path");
const webpack = require("webpack");
// const UglifyJSPlugin = require("uglifyjs-webpack-plugin");

module.exports = {
  entry: {
    home: path.resolve(__dirname, "src/js/home.js")
  },
  output: {
    path: path.resolve(__dirname, "public/js"),
    publicPath: "/public/",
    filename: "[name].bundle.js"
  },
  //   plugins: [
  //     new UglifyJSPlugin({
  //       sourceMap: true
  //     })
  //   ],
  module: {
    rules: [
      {
        test: /\.js$/,
        loader: "babel-loader",
        exclude: /node_modules/
      }
    ]
  }
};
